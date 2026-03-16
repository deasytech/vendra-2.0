<?php

namespace App\Livewire\Invoices;

use App\Exceptions\TaxlyApiException;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\TaxlyService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExchangeInvoices extends Component
{
  use WithPagination;

  public $search = '';
  public $statusFilter = '';
  public $dateFrom = '';
  public $dateTo = '';

  // Sync modal
  public $showSyncModal = false;
  public $syncResults = [];
  public $syncMessage = '';
  public $syncError = null;

  protected $queryString = [
    'search' => ['except' => ''],
    'statusFilter' => ['except' => ''],
    'dateFrom' => ['except' => ''],
    'dateTo' => ['except' => ''],
  ];

  public function confirmExchangeInvoice($invoiceId)
  {
    try {
      $invoice = Invoice::findOrFail($invoiceId);

      if (!$invoice->irn) {
        $this->dispatch('error', 'Invoice does not have an IRN number');
        return;
      }

      // Get the Taxly tenant_id from settings (the one from Taxly integrator registration)
      $taxlyTenantId = Setting::getValue('taxly_tenant_id');

      if (!$taxlyTenantId) {
        $this->dispatch('error', 'Taxly tenant ID not configured. Please register as an integrator first.');
        return;
      }

      // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
      $taxlyCredential = \App\Models\TaxlyCredential::withoutGlobalScopes()
        ->where('tenant_id', $taxlyTenantId)
        ->first();

      if (!$taxlyCredential || empty($taxlyCredential->api_key)) {
        $this->dispatch('error', 'Taxly API key not configured. Please generate an API key first.');
        return;
      }

      $taxlyService = new TaxlyService($taxlyCredential);
      $result = $taxlyService->confirmExchangeInvoice($invoice->irn);

      // Log the confirmation
      $invoice->transmissions()->create([
        'action' => 'confirm_exchange',
        'request_payload' => ['irn' => $invoice->irn],
        'response_payload' => $result,
        'status' => ($result['success'] ?? false) ? 'success' : 'failed',
      ]);

      if ($result['success'] ?? false) {
        $invoice->update(['transmit' => 'CONFIRMED']);
        $this->dispatch('success', 'Exchange invoice confirmed successfully');
      } else {
        $this->dispatch('error', 'Invoice confirmation failed: ' . ($result['message'] ?? 'Unknown error'));
      }
    } catch (\Exception $e) {
      Log::error('Exchange invoice confirmation failed', [
        'invoice_id' => $invoiceId,
        'error' => $e->getMessage()
      ]);

      $this->dispatch('error', 'Failed to confirm invoice: ' . $e->getMessage());
    }
  }

  public function downloadExchangeInvoice($invoiceId)
  {
    try {
      $invoice = Invoice::findOrFail($invoiceId);

      if (!$invoice->irn) {
        $this->dispatch('error', 'Invoice does not have an IRN number');
        return;
      }

      // Get the Taxly tenant_id from settings (the one from Taxly integrator registration)
      $taxlyTenantId = Setting::getValue('taxly_tenant_id');

      if (!$taxlyTenantId) {
        $this->dispatch('error', 'Taxly tenant ID not configured. Please register as an integrator first.');
        return;
      }

      // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
      $taxlyCredential = \App\Models\TaxlyCredential::withoutGlobalScopes()
        ->where('tenant_id', $taxlyTenantId)
        ->first();

      if (!$taxlyCredential || empty($taxlyCredential->api_key)) {
        $this->dispatch('error', 'Taxly API key not configured. Please generate an API key first.');
        return;
      }

      $taxlyService = new TaxlyService($taxlyCredential);
      $result = $taxlyService->downloadExchangeInvoice($invoice->irn);

      // Log the download action
      $invoice->transmissions()->create([
        'action' => 'download_exchange',
        'request_payload' => ['irn' => $invoice->irn],
        'response_payload' => $result,
        'status' => 'success'
      ]);

      // Redirect to the download URL if present
      if (isset($result['data']['download_url'])) {
        return redirect()->away($result['data']['download_url']);
      } elseif (isset($result['download_url'])) {
        return redirect()->away($result['download_url']);
      } else {
        $this->dispatch('error', 'Download URL not available in response');
      }
    } catch (\Exception $e) {
      Log::error('Exchange invoice download failed', [
        'invoice_id' => $invoiceId,
        'error' => $e->getMessage()
      ]);

      $this->dispatch('error', 'Failed to download invoice: ' . $e->getMessage());
    }
  }

  public function syncFromTaxly()
  {
    try {
      $this->showSyncModal = true;
      $this->syncMessage = 'Syncing invoices from Taxly...';

      $organization = Auth::user()->organization;

      if (!$organization || !$organization->business_id) {
        $this->syncMessage = 'Organization Business ID not configured. Please ensure your Taxly integration is set up correctly.';
        $this->syncResults = [];
        return;
      }

      // Get the Taxly tenant_id from settings (the one from Taxly integrator registration)
      $taxlyTenantId = Setting::getValue('taxly_tenant_id');

      if (!$taxlyTenantId) {
        $this->syncMessage = 'Taxly tenant ID not configured. Please register as an integrator first.';
        $this->syncResults = [];
        return;
      }

      // Get the TaxlyCredential using the Taxly tenant_id to use the generated API key
      // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
      $taxlyCredential = \App\Models\TaxlyCredential::withoutGlobalScopes()
        ->where('tenant_id', $taxlyTenantId)
        ->first();

      Log::info('Retrieved Taxly credential for sync', [
        'taxly_tenant_id' => $taxlyTenantId,
        'credential_exists' => $taxlyCredential ? true : false,
        'api_key_set' => $taxlyCredential && !empty($taxlyCredential->api_key) ? true : false,
      ]);

      if (!$taxlyCredential || empty($taxlyCredential->api_key)) {
        $this->syncMessage = 'Taxly API key not configured. Please generate an API key first.';
        $this->syncResults = [];
        return;
      }

      $taxlyService = new TaxlyService($taxlyCredential);

      // Build filters
      $filters = [];
      if ($this->statusFilter) {
        $filters['status'] = $this->statusFilter;
      }
      if ($this->dateFrom) {
        $filters['date_from'] = $this->dateFrom;
      }
      if ($this->dateTo) {
        $filters['date_to'] = $this->dateTo;
      }

      // Use business_id (UUID) instead of tin for the API call
      $result = $taxlyService->searchExchangeInvoices($organization->business_id, $filters);

      Log::info('Taxly exchange invoice search result', [
        'filters' => $filters,
        'result_summary' => [
          'success' => $result['success'] ?? false,
          'message' => $result['message'] ?? null,
          'data_count' => isset($result['data']) && is_array($result['data']) ? count($result['data']) : null,
        ]
      ]);

      if (isset($result['data']) && is_array($result['data'])) {
        $syncedCount = 0;
        $existingCount = 0;
        $skippedCount = 0;

        foreach ($result['data'] as $exchangeInvoice) {
          // Skip if no IRN is provided
          if (empty($exchangeInvoice['irn'])) {
            Log::warning('Exchange invoice missing IRN, skipping', ['invoice' => $exchangeInvoice]);
            $skippedCount++;
            continue;
          }

          // Check if invoice already exists
          $existing = Invoice::where('irn', $exchangeInvoice['irn'])->first();
          if ($existing) {
            $existingCount++;
            continue;
          }

          // Create new invoice from exchange data
          $this->createInvoiceFromExchangeData($exchangeInvoice, $organization);
          $syncedCount++;
        }

        $this->syncResults = [
          'synced' => $syncedCount,
          'existing' => $existingCount,
          'skipped' => $skippedCount,
          'total' => count($result['data'])
        ];

        $summaryParts = [
          "{$syncedCount} new invoices",
          "{$existingCount} already existed",
        ];

        if ($skippedCount > 0) {
          $summaryParts[] = "{$skippedCount} skipped";
        }

        $this->syncMessage = 'Sync completed: ' . implode(', ', $summaryParts)
          . ' out of ' . count($result['data']) . ' returned by Taxly';
      } else {
        $this->syncMessage = 'No invoices found or invalid response from Taxly';
        $this->syncResults = [];
      }
    } catch (\Exception $e) {
      Log::error('Exchange invoice sync failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $this->syncMessage = 'Sync failed';

      // Extract details from the custom exception if available
      $details = null;
      if ($e instanceof TaxlyApiException) {
        $details = $e->getDetails();
      }

      $this->syncError = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'type' => get_class($e),
        'details' => $details,
      ];
      $this->syncResults = [];
    }
  }

  protected function createInvoiceFromExchangeData(array $exchangeInvoice, $organization)
  {
    $invoiceData = $exchangeInvoice['invoice_data']
      ?? $exchangeInvoice['decrypted_invoice']
      ?? [];
    $sellerDetails = $invoiceData['sellerDetails'] ?? [];

    // Find or create customer
    $customer = \App\Models\Customer::firstOrCreate(
      [
        'tenant_id' => $organization->tenant_id,
        'tin' => $sellerDetails['tin'] ?? null
      ],
      [
        'name' => $sellerDetails['name'] ?? 'Unknown Supplier',
        'address' => $sellerDetails['address'] ?? null,
        'status' => 'active'
      ]
    );

    $taxExclusiveAmount = (float) data_get($invoiceData, 'taxExclusiveAmount', data_get($invoiceData, 'subtotal', data_get($invoiceData, 'totalAmount', 0)));
    $taxInclusiveAmount = (float) data_get($invoiceData, 'taxInclusiveAmount', data_get($invoiceData, 'totalAmount', 0));
    $payableAmount = (float) data_get($invoiceData, 'payableAmount', data_get($invoiceData, 'totalAmount', $taxInclusiveAmount));

    $legalMonetaryTotal = [
      'tax_exclusive_amount' => $taxExclusiveAmount,
      'tax_inclusive_amount' => $taxInclusiveAmount,
      'payable_amount' => $payableAmount,
    ];

    $accountingSupplierParty = [
      'party_name' => $sellerDetails['name'] ?? '',
      'party_tin' => $sellerDetails['tin'] ?? '',
      'party_address' => $sellerDetails['address'] ?? '',
    ];

    $buyerDetails = $invoiceData['buyerDetails'] ?? [];
    $accountingCustomerParty = [
      'party_name' => $buyerDetails['name'] ?? '',
      'party_tin' => $buyerDetails['tin'] ?? '',
      'party_address' => $buyerDetails['address'] ?? '',
    ];

    $invoice = Invoice::create([
      'tenant_id' => $organization->tenant_id,
      'organization_id' => $organization->id,
      'customer_id' => $customer->id,
      'invoice_reference' => $invoiceData['invoiceNumber'] ?? $exchangeInvoice['irn'],
      'irn' => $exchangeInvoice['irn'],
      'issue_date' => $invoiceData['invoiceDate'] ?? now()->format('Y-m-d'),
      'due_date' => $invoiceData['dueDate'] ?? null,
      'invoice_type_code' => $invoiceData['invoiceType'] ?? 'STANDARD',
      'document_currency_code' => $invoiceData['currency'] ?? 'NGN',
      'payment_status' => 'PENDING',
      'note' => ['text' => $exchangeInvoice['source'] ?? 'FIRS_EXCHANGE'],
      'accounting_supplier_party' => $accountingSupplierParty,
      'accounting_customer_party' => $accountingCustomerParty,
      'legal_monetary_total' => $legalMonetaryTotal,
      'metadata' => [
        'source' => $exchangeInvoice['source'] ?? 'FIRS_EXCHANGE',
        'direction' => $exchangeInvoice['direction'] ?? 'INCOMING',
        'received_at' => $exchangeInvoice['received_at'] ?? now()->toIso8601String(),
        'status' => $exchangeInvoice['status'] ?? 'TRANSMITTED',
        'buyer_tin' => $exchangeInvoice['buyer_tin'] ?? ($buyerDetails['tin'] ?? null),
        'seller_tin' => $exchangeInvoice['seller_tin'] ?? ($sellerDetails['tin'] ?? null),
        'decrypted_invoice' => $invoiceData,
      ],
      'transmit' => 'RECEIVED',
      'delivered' => true,
    ]);

    // Create invoice lines
    if (isset($invoiceData['items']) && is_array($invoiceData['items'])) {
      foreach ($invoiceData['items'] as $index => $item) {
        \App\Models\InvoiceLine::create([
          'invoice_id' => $invoice->id,
          'hsn_code' => $item['hsnCode'] ?? $item['hsn_code'] ?? 'GENERAL',
          'product_category' => $item['productCategory'] ?? $item['product_category'] ?? 'General Items',
          'invoiced_quantity' => (int) ($item['quantity'] ?? $item['invoiced_quantity'] ?? 1),
          'line_extension_amount' => (float) ($item['totalPrice'] ?? $item['line_extension_amount'] ?? (($item['unitPrice'] ?? data_get($item, 'price.price_amount', 0)) * ($item['quantity'] ?? 1))),
          'item' => [
            'name' => $item['name'] ?? $item['description'] ?? 'Item',
            'description' => $item['description'] ?? $item['name'] ?? 'Item',
          ],
          'price' => [
            'price_amount' => (float) ($item['unitPrice'] ?? data_get($item, 'price.price_amount', 0)),
            'base_quantity' => (float) data_get($item, 'price.base_quantity', 1),
            'price_unit' => ($invoiceData['currency'] ?? 'NGN') . ' per 1',
          ],
          'order' => $index,
        ]);
      }
    }

    // Create tax totals
    if (isset($invoiceData['taxAmount']) && $invoiceData['taxAmount'] > 0) {
      \App\Models\InvoiceTaxTotal::create([
        'invoice_id' => $invoice->id,
        'tax_amount' => (float) $invoiceData['taxAmount'],
        'tax_subtotal' => [[
          'taxable_amount' => $taxExclusiveAmount,
          'tax_amount' => (float) $invoiceData['taxAmount'],
          'tax_category_id' => 'VAT',
          'tax_percentage' => (float) ($invoiceData['items'][0]['taxRate'] ?? 7.5),
        ]],
      ]);
    }

    return $invoice;
  }

  public function closeSyncModal()
  {
    $this->showSyncModal = false;
    $this->syncResults = [];
    $this->syncMessage = '';
    $this->syncError = null;
  }

  public function render()
  {
    $user = Auth::user();
    $organization = $user?->organization;
    $organizationTin = $organization?->tin;

    $query = Invoice::with(['customer', 'organization', 'transmissions'])
      ->where(function ($q) {
        $q->where('metadata->invoice_flow', 'incoming')
          ->orWhere('transmit', 'RECEIVED')
          ->orWhere('transmit', 'ACKNOWLEDGED');
      });

    // Filter by metadata JSON - invoices from FIRS Exchange
    $query->where(function ($q) {
      $q->where('metadata->invoice_flow', 'incoming')
        ->orWhere('metadata->source', 'FIRS_EXCHANGE')
        ->orWhere('metadata->direction', 'INCOMING');
    });

    if ($organization) {
      $query->where('organization_id', $organization->id);
    }

    if ($organizationTin) {
      $query->where(function ($q) use ($organizationTin) {
        $q->where('metadata->buyer_tin', $organizationTin)
          ->orWhere('accounting_customer_party->party_tin', $organizationTin);
      });
    }

    if ($this->statusFilter) {
      $query->where(function ($q) {
        $q->where('transmit', $this->statusFilter)
          ->orWhere('metadata->status', $this->statusFilter);
      });
    }

    // Apply search
    if ($this->search) {
      $query->where(function ($q) {
        $q->where('invoice_reference', 'like', '%' . $this->search . '%')
          ->orWhere('irn', 'like', '%' . $this->search . '%')
          ->orWhereHas('customer', function ($cq) {
            $cq->where('name', 'like', '%' . $this->search . '%');
          });
      });
    }

    // Apply date filters
    if ($this->dateFrom) {
      $query->whereDate('issue_date', '>=', $this->dateFrom);
    }
    if ($this->dateTo) {
      $query->whereDate('issue_date', '<=', $this->dateTo);
    }

    $invoices = $query->latest()->paginate(10);

    return view('livewire.invoices.exchange-invoices', [
      'invoices' => $invoices,
      'organizationTin' => $organizationTin,
    ]);
  }
}
