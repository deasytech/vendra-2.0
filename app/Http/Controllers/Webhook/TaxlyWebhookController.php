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
    if ($this->isExchangeInvoiceWebhook($request, $eventType)) {
      return $this->handleExchangeInvoice($request);
    }

    // Handle transmission webhook response from transmitByIrn
    $irn = $request->input('irn');
    $status = $request->input('status');
    $transmissionId = $request->input('transmission_id');
    $message = $request->input('message');
    $error = $request->input('error');
    $transmittedAt = $request->input('transmitted_at');
    $responsePayload = $request->input('response', []);

    if ($irn) {
      $invoice = Invoice::where('irn', $irn)->first();

      if ($invoice) {
        // Handle different webhook status updates
        if (strtolower((string) $status) === 'transmitting') {
          // Intermediate status: transmission has started but not completed
          $invoice->update(['transmit' => 'TRANSMITTING']);
        } else {
          // Final status: transmission completed (success or failed)
          $isSuccessfulTransmission = in_array(strtolower((string) $status), ['success', 'completed', 'transmitted'], true)
            || (data_get($responsePayload, 'code') === 200 && data_get($responsePayload, 'data.ok') === true)
            || data_get($request->all(), 'data.ok') === true;

          $transmitStatus = $isSuccessfulTransmission ? 'TRANSMITTED' : 'FAILED';
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

      $validated = $this->normalizeExchangePayload($request);

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

      // Check if this incoming invoice already exists for the buyer organization
      $existingInvoice = Invoice::withoutGlobalScopes()
        ->where('irn', $validated['irn'])
        ->where('organization_id', $organization->id)
        ->where(function ($query) {
          $query->where('metadata->invoice_flow', 'incoming')
            ->orWhere('transmit', 'RECEIVED')
            ->orWhere('metadata->direction', 'INCOMING')
            ->orWhere('metadata->source', 'FIRS_EXCHANGE');
        })
        ->first();
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
      $sellerDetails = $invoiceData['sellerDetails']
        ?? $invoiceData['accounting_supplier_party']
        ?? [];

      $customer = $this->findOrCreateCustomer($sellerDetails, $organization->tenant_id);

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
    $tin = $sellerDetails['tin'] ?? $sellerDetails['party_tin'] ?? null;

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
      'name' => $sellerDetails['name'] ?? $sellerDetails['party_name'] ?? 'Unknown Supplier',
      'tin' => $tin,
      'address' => $sellerDetails['address'] ?? $sellerDetails['party_address'] ?? data_get($sellerDetails, 'postal_address.street_name'),
      'status' => 'active'
    ]);
  }

  /**
   * Create exchange invoice from webhook data
   */
  protected function createExchangeInvoice(array $webhookData, array $invoiceData, Organization $organization, Customer $customer): Invoice
  {
    $taxExclusiveAmount = (float) data_get(
      $invoiceData,
      'legal_monetary_total.tax_exclusive_amount',
      data_get($invoiceData, 'taxExclusiveAmount', data_get($invoiceData, 'subtotal', data_get($invoiceData, 'totalAmount', 0)))
    );
    $taxInclusiveAmount = (float) data_get(
      $invoiceData,
      'legal_monetary_total.tax_inclusive_amount',
      data_get($invoiceData, 'taxInclusiveAmount', data_get($invoiceData, 'totalAmount', 0))
    );
    $payableAmount = (float) data_get(
      $invoiceData,
      'legal_monetary_total.payable_amount',
      data_get($invoiceData, 'payableAmount', data_get($invoiceData, 'totalAmount', $taxInclusiveAmount))
    );
    $lineExtensionAmount = (float) data_get(
      $invoiceData,
      'legal_monetary_total.line_extension_amount',
      data_get($invoiceData, 'lineExtensionAmount', $taxExclusiveAmount)
    );

    $legalMonetaryTotal = [
      'tax_exclusive_amount' => $taxExclusiveAmount,
      'tax_inclusive_amount' => $taxInclusiveAmount,
      'payable_amount' => $payableAmount,
      'line_extension_amount' => $lineExtensionAmount,
    ];

    $accountingSupplierParty = [
      'party_name' => data_get($invoiceData, 'sellerDetails.name', data_get($invoiceData, 'accounting_supplier_party.party_name', '')),
      'party_tin' => data_get($invoiceData, 'sellerDetails.tin', data_get($invoiceData, 'accounting_supplier_party.tin', '')),
      'party_address' => data_get($invoiceData, 'sellerDetails.address', data_get($invoiceData, 'accounting_supplier_party.postal_address.street_name', '')),
    ];

    $accountingCustomerParty = [
      'party_name' => data_get($invoiceData, 'buyerDetails.name', data_get($invoiceData, 'accounting_customer_party.party_name', '')),
      'party_tin' => data_get($invoiceData, 'buyerDetails.tin', data_get($invoiceData, 'accounting_customer_party.tin', '')),
      'party_address' => data_get($invoiceData, 'buyerDetails.address', data_get($invoiceData, 'accounting_customer_party.postal_address.street_name', '')),
    ];

    $invoiceReference = $this->resolveIncomingInvoiceReference(
      $invoiceData['invoiceNumber'] ?? data_get($invoiceData, 'invoice_number', $webhookData['irn']),
      $organization->id
    );

    $invoice = Invoice::create([
      'tenant_id' => $organization->tenant_id,
      'organization_id' => $organization->id,
      'customer_id' => $customer->id,
      'invoice_reference' => $invoiceReference,
      'irn' => $webhookData['irn'],
      'issue_date' => $invoiceData['invoiceDate'] ?? data_get($invoiceData, 'issue_date', now()->format('Y-m-d')),
      'due_date' => $invoiceData['dueDate'] ?? data_get($invoiceData, 'due_date'),
      'invoice_type_code' => $invoiceData['invoiceType'] ?? data_get($invoiceData, 'invoice_type_code', 'STANDARD'),
      'document_currency_code' => $invoiceData['currency'] ?? data_get($invoiceData, 'document_currency_code', 'NGN'),
      'payment_status' => data_get($invoiceData, 'payment_status', 'PENDING'),
      'note' => ['text' => $webhookData['source'] ?? 'FIRS_EXCHANGE'],
      'payment_terms_note' => null,
      'accounting_supplier_party' => $accountingSupplierParty,
      'accounting_customer_party' => $accountingCustomerParty,
      'legal_monetary_total' => $legalMonetaryTotal,
      'metadata' => [
        'invoice_flow' => 'incoming',
        'source' => $webhookData['source'],
        'direction' => $webhookData['direction'],
        'received_at' => $webhookData['received_at'],
        'status' => $webhookData['status'],
        'buyer_tin' => $webhookData['buyer_tin'],
        'seller_tin' => $webhookData['seller_tin'],
        'event' => $webhookData['event'] ?? 'exchange_invoice.received',
        'decrypted_invoice' => $webhookData['raw_invoice_data'] ?? $invoiceData,
      ],
      'transmit' => 'ACKNOWLEDGED',
      'delivered' => true,
    ]);

    // Create invoice lines
    $invoiceLines = $invoiceData['items'] ?? $invoiceData['invoice_line'] ?? [];
    if (is_array($invoiceLines)) {
      foreach ($invoiceLines as $index => $item) {
        InvoiceLine::create([
          'invoice_id' => $invoice->id,
          'hsn_code' => $item['hsnCode'] ?? $item['hsn_code'] ?? 'GENERAL',
          'product_category' => $item['productCategory'] ?? $item['product_category'] ?? 'General Items',
          'invoiced_quantity' => (int) ($item['quantity'] ?? $item['invoiced_quantity'] ?? 1),
          'line_extension_amount' => (float) ($item['totalPrice'] ?? $item['line_extension_amount'] ?? (($item['unitPrice'] ?? data_get($item, 'price.price_amount', 0)) * ($item['quantity'] ?? $item['invoiced_quantity'] ?? 1))),
          'item' => [
            'name' => $item['name'] ?? data_get($item, 'item.name', $item['description'] ?? 'Item'),
            'description' => $item['description'] ?? data_get($item, 'item.description', $item['name'] ?? 'Item'),
          ],
          'price' => [
            'price_amount' => (float) ($item['unitPrice'] ?? data_get($item, 'price.price_amount', 0)),
            'base_quantity' => (float) data_get($item, 'price.base_quantity', 1),
            'price_unit' => ($invoiceData['currency'] ?? data_get($invoiceData, 'document_currency_code', 'NGN')) . ' per 1',
          ],
          'order' => $index,
        ]);
      }
    }

    // Create tax totals
    $taxAmount = (float) ($invoiceData['taxAmount'] ?? data_get($invoiceData, 'tax_total.0.tax_amount', 0));
    if ($taxAmount > 0) {
      InvoiceTaxTotal::create([
        'invoice_id' => $invoice->id,
        'tax_amount' => $taxAmount,
        'tax_subtotal' => data_get($invoiceData, 'tax_total.0.tax_subtotal')
          ? collect(data_get($invoiceData, 'tax_total.0.tax_subtotal'))->map(function ($subtotal) {
            return [
              'taxable_amount' => (float) ($subtotal['taxable_amount'] ?? 0),
              'tax_amount' => (float) ($subtotal['tax_amount'] ?? 0),
              'tax_category_id' => data_get($subtotal, 'tax_category.id', 'VAT'),
              'tax_percentage' => (float) data_get($subtotal, 'tax_category.percent', 7.5),
            ];
          })->values()->all()
          : [[
            'taxable_amount' => $taxExclusiveAmount,
            'tax_amount' => $taxAmount,
            'tax_category_id' => 'VAT',
            'tax_percentage' => 7.5,
          ]],
      ]);
    }

    return $invoice;
  }

  protected function resolveIncomingInvoiceReference(string $baseReference, int $organizationId): string
  {
    $reference = $baseReference;

    $exists = Invoice::withoutGlobalScopes()
      ->where('invoice_reference', $reference)
      ->exists();

    if (!$exists) {
      return $reference;
    }

    $reference = $baseReference . '-IN';

    $counter = 1;
    while (
      Invoice::withoutGlobalScopes()
      ->where('invoice_reference', $reference)
      ->exists()
    ) {
      $reference = $baseReference . '-IN-' . $counter;
      $counter++;
    }

    return $reference;
  }

  protected function isExchangeInvoiceWebhook(Request $request, ?string $eventType): bool
  {
    if ($eventType === 'exchange_invoice.received') {
      return true;
    }

    $event = (string) ($request->input('event') ?? '');

    return str_contains($event, 'exchange_invoice')
      || str_contains($event, 'exchange.invoice')
      || str_contains($event, 'decrypted')
      || $request->has('invoice_data')
      || $request->has('decrypted_invoice')
      || $request->has('payload.invoice_data')
      || $request->has('payload.decrypted_invoice');
  }

  protected function normalizeExchangePayload(Request $request): array
  {
    $payload = $request->all();
    $container = is_array($payload['payload'] ?? null) ? $payload['payload'] : $payload;

    $invoiceData = $container['invoice_data']
      ?? $container['decrypted_invoice']
      ?? $container['invoice_payload']
      ?? data_get($container, 'data.invoice_data')
      ?? data_get($container, 'data.decrypted_invoice')
      ?? data_get($container, 'data.invoice_payload')
      ?? [];

    if (!is_array($invoiceData)) {
      $invoiceData = [];
    }

    $buyerTin = (string) (
      $container['buyer_tin']
      ?? $container['customer_tin']
      ?? data_get($invoiceData, 'buyerDetails.tin')
      ?? data_get($invoiceData, 'buyer.tin')
      ?? data_get($invoiceData, 'accounting_customer_party.tin')
      ?? data_get($invoiceData, 'accounting_customer_party.party_tin')
      ?? ''
    );

    $sellerTin = (string) (
      $container['seller_tin']
      ?? $container['supplier_tin']
      ?? data_get($invoiceData, 'sellerDetails.tin')
      ?? data_get($invoiceData, 'seller.tin')
      ?? data_get($invoiceData, 'accounting_supplier_party.tin')
      ?? data_get($invoiceData, 'accounting_supplier_party.party_tin')
      ?? ''
    );

    return validator([
      'irn' => (string) ($container['irn'] ?? data_get($invoiceData, 'irn') ?? data_get($invoiceData, 'invoiceNumber') ?? ''),
      'direction' => strtoupper((string) ($container['direction'] ?? 'INCOMING')),
      'status' => (string) ($container['status'] ?? 'received'),
      'buyer_tin' => $buyerTin,
      'seller_tin' => $sellerTin,
      'source' => (string) ($container['source'] ?? 'FIRS_EXCHANGE'),
      'received_at' => (string) ($container['received_at'] ?? $container['timestamp'] ?? now()->toIso8601String()),
      'invoice_data' => $invoiceData,
      'event' => $container['event'] ?? $request->header('X-Webhook-Event') ?? 'exchange_invoice.received',
      'raw_invoice_data' => $invoiceData,
    ], [
      'irn' => 'required|string',
      'direction' => 'required|string|in:INCOMING',
      'status' => 'required|string',
      'buyer_tin' => 'required|string',
      'seller_tin' => 'required|string',
      'source' => 'required|string',
      'received_at' => 'required|string',
      'invoice_data' => 'required|array',
    ])->validate();
  }
}
