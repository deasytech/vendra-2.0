<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoiceTaxTotal;
use App\Models\InvoiceTransmission;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaxlyWebhookController extends Controller
{
  /**
   * Handle transmission webhook response from transmitByIrn
   */
  public function handle(Request $request)
  {
    Log::info('Taxly webhook received', [
      'headers' => $request->headers->all(),
      'payload' => $request->all()
    ]);

    // Check if this is an exchange invoice webhook
    $eventType = $request->header('X-Webhook-Event');
    if ($eventType === 'exchange_invoice.received') {
      return $this->handleExchangeInvoice($request);
    }

    // Handle transmission webhook response from transmitByIrn
    $irn = $request->input('irn');
    $status = $request->input('status');
    $transmissionId = $request->input('transmission_id');
    $message = $request->input('message');
    $error = $request->input('error');
    $transmittedAt = $request->input('transmitted_at');

    if ($irn) {
      $invoice = Invoice::where('irn', $irn)->first();

      if ($invoice) {
        // Handle different webhook status updates
        if ($status === 'transmitting') {
          // Intermediate status: transmission has started but not completed
          $invoice->update(['transmit' => 'TRANSMITTING']);
        } else {
          // Final status: transmission completed (success or failed)
          $transmitStatus = $status === 'success' ? 'TRANSMITTED' : 'FAILED';
          $invoice->update(['transmit' => $transmitStatus]);
        }

        // Create or update transmission record
        $transmissionData = [
          'invoice_id' => $invoice->id,
          'irn' => $irn,
          'action' => 'transmitted', // Set the action for transmission webhook
          'status' => $status,
          'message' => $message,
          'error' => $error,
          'transmitted_at' => $transmittedAt ? now()->parse($transmittedAt) : now(),
        ];

        if ($transmissionId) {
          $transmissionData['id'] = $transmissionId;
        }

        InvoiceTransmission::updateOrCreate(
          [
            'invoice_id' => $invoice->id,
            'irn' => $irn
          ],
          $transmissionData
        );

        Log::info('Invoice transmission updated', [
          'irn' => $irn,
          'status' => $status,
          'invoice_id' => $invoice->id
        ]);
      } else {
        Log::warning('Invoice not found for IRN', ['irn' => $irn]);
      }
    } else {
      Log::warning('No IRN provided in webhook payload');
    }

    return response()->json(['status' => 'success', 'message' => 'Webhook processed']);
  }

  /**
   * Handle incoming exchange invoice webhook from Taxly/FIRS Exchange
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function handleExchangeInvoice(Request $request)
  {
    try {
      Log::info('Taxly exchange invoice webhook received', [
        'headers' => $request->headers->all(),
        'payload' => $request->all()
      ]);

      // Validate required fields
      $validated = $request->validate([
        'irn' => 'required|string',
        'direction' => 'required|string|in:INCOMING',
        'status' => 'required|string',
        'buyer_tin' => 'required|string',
        'seller_tin' => 'required|string',
        'source' => 'required|string',
        'received_at' => 'required|string',
        'invoice_data' => 'required|array',
      ]);

      // Find the organization by buyer TIN
      $organization = Organization::where('tin', $validated['buyer_tin'])->first();

      if (!$organization) {
        Log::error('Organization not found for TIN', ['tin' => $validated['buyer_tin']]);
        return response()->json([
          'status' => 'error',
          'message' => 'Organization not found',
          'error_code' => 'TENANT_NOT_FOUND'
        ], 404);
      }

      // Check if invoice already exists
      $existingInvoice = Invoice::where('irn', $validated['irn'])->first();
      if ($existingInvoice) {
        Log::info('Invoice already exists', ['irn' => $validated['irn']]);
        return response()->json([
          'status' => 'received',
          'message' => 'Invoice already processed',
          'your_invoice_id' => $existingInvoice->id
        ], 200);
      }

      // Extract invoice data
      $invoiceData = $validated['invoice_data'];

      // Find or create customer (seller)
      $customer = $this->findOrCreateCustomer($invoiceData['sellerDetails'] ?? [], $organization->tenant_id);

      // Create the invoice
      $invoice = $this->createExchangeInvoice($validated, $invoiceData, $organization, $customer);

      Log::info('Exchange invoice created successfully', [
        'invoice_id' => $invoice->id,
        'irn' => $validated['irn']
      ]);

      return response()->json([
        'status' => 'received',
        'message' => 'Invoice processed successfully',
        'your_invoice_id' => $invoice->id
      ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
      Log::error('Webhook validation failed', ['errors' => $e->errors()]);
      return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      Log::error('Webhook processing failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return response()->json([
        'status' => 'error',
        'message' => 'Failed to process invoice',
        'error_code' => 'PROCESSING_FAILED'
      ], 500);
    }
  }

  /**
   * Find or create customer from seller details
   */
  protected function findOrCreateCustomer(array $sellerDetails, int $tenantId): Customer
  {
    $tin = $sellerDetails['tin'] ?? null;

    if ($tin) {
      $customer = Customer::where('tin', $tin)
        ->where('tenant_id', $tenantId)
        ->first();

      if ($customer) {
        return $customer;
      }
    }

    return Customer::create([
      'tenant_id' => $tenantId,
      'name' => $sellerDetails['name'] ?? 'Unknown Supplier',
      'tin' => $tin,
      'address' => $sellerDetails['address'] ?? null,
      'status' => 'active'
    ]);
  }

  /**
   * Create exchange invoice from webhook data
   */
  protected function createExchangeInvoice(array $webhookData, array $invoiceData, Organization $organization, Customer $customer): Invoice
  {
    $legalMonetaryTotal = [
      'tax_exclusive_amount' => $invoiceData['totalAmount'] ?? 0,
      'tax_inclusive_amount' => $invoiceData['totalAmount'] ?? 0,
      'payable_amount' => $invoiceData['totalAmount'] ?? 0,
    ];

    $accountingSupplierParty = [
      'party_name' => $invoiceData['sellerDetails']['name'] ?? '',
      'party_tin' => $invoiceData['sellerDetails']['tin'] ?? '',
      'party_address' => $invoiceData['sellerDetails']['address'] ?? '',
    ];

    $accountingCustomerParty = [
      'party_name' => $invoiceData['buyerDetails']['name'] ?? '',
      'party_tin' => $invoiceData['buyerDetails']['tin'] ?? '',
      'party_address' => $invoiceData['buyerDetails']['address'] ?? '',
    ];

    $invoice = Invoice::create([
      'tenant_id' => $organization->tenant_id,
      'organization_id' => $organization->id,
      'customer_id' => $customer->id,
      'invoice_reference' => $invoiceData['invoiceNumber'] ?? $webhookData['irn'],
      'irn' => $webhookData['irn'],
      'issue_date' => $invoiceData['invoiceDate'] ?? now()->format('Y-m-d'),
      'due_date' => $invoiceData['dueDate'] ?? null,
      'invoice_type_code' => $invoiceData['invoiceType'] ?? 'STANDARD',
      'document_currency_code' => $invoiceData['currency'] ?? 'NGN',
      'payment_status' => 'PENDING',
      'note' => ['text' => $webhookData['source'] ?? 'FIRS_EXCHANGE'],
      'payment_terms_note' => null,
      'accounting_supplier_party' => $accountingSupplierParty,
      'accounting_customer_party' => $accountingCustomerParty,
      'legal_monetary_total' => $legalMonetaryTotal,
      'metadata' => [
        'source' => $webhookData['source'],
        'direction' => $webhookData['direction'],
        'received_at' => $webhookData['received_at'],
        'status' => $webhookData['status'],
      ],
      'transmit' => 'RECEIVED',
      'delivered' => true,
    ]);

    // Create invoice lines
    if (isset($invoiceData['items']) && is_array($invoiceData['items'])) {
      foreach ($invoiceData['items'] as $item) {
        InvoiceLine::create([
          'invoice_id' => $invoice->id,
          'description' => $item['description'] ?? '',
          'invoiced_quantity' => $item['quantity'] ?? 1,
          'unit_price' => $item['unitPrice'] ?? 0,
          'line_extension_amount' => $item['totalPrice'] ?? 0,
          'tax_amount' => $item['taxAmount'] ?? 0,
          'tax_rate' => $item['taxRate'] ?? 0,
        ]);
      }
    }

    // Create tax totals
    if (isset($invoiceData['taxAmount']) && $invoiceData['taxAmount'] > 0) {
      InvoiceTaxTotal::create([
        'invoice_id' => $invoice->id,
        'tax_category_id' => 'VAT',
        'tax_amount' => $invoiceData['taxAmount'],
        'tax_percentage' => $invoiceData['items'][0]['taxRate'] ?? 7.5,
      ]);
    }

    return $invoice;
  }
}
