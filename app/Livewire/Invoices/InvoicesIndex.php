<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\TaxlyService;
use App\Models\TaxlyCredential;
use App\Jobs\SubmitInvoiceJob;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicesIndex extends Component
{
    use WithPagination;

    // Filter properties
    public $search = '';
    public $customer_id = '';
    public $payment_status = '';
    public $transmit_status = '';
    public $currency = '';
    public $date_from = '';
    public $date_to = '';
    public $amount_min = '';
    public $amount_max = '';
    public $selectedInvoiceId = null;
    public $newPaymentStatus = 'PENDING';

    protected $queryString = [
        'search' => ['except' => ''],
        'customer_id' => ['except' => ''],
        'payment_status' => ['except' => ''],
        'transmit_status' => ['except' => ''],
        'currency' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'amount_min' => ['except' => ''],
        'amount_max' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCustomerId()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus()
    {
        $this->resetPage();
    }

    public function updatingTransmitStatus()
    {
        $this->resetPage();
    }

    public function updatingCurrency()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingAmountMin()
    {
        $this->resetPage();
    }

    public function updatingAmountMax()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'customer_id',
            'payment_status',
            'transmit_status',
            'currency',
            'date_from',
            'date_to',
            'amount_min',
            'amount_max',
        ]);
        $this->resetPage();
    }

    public function render()
    {
        $query = Invoice::with(['customer', 'organization'])
            ->where(function ($q) {
                $q->whereNull('metadata->invoice_flow')
                    ->orWhere('metadata->invoice_flow', '!=', 'incoming');
            });

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_reference', 'like', '%' . $this->search . '%')
                    ->orWhere('irn', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->customer_id) {
            $query->where('customer_id', $this->customer_id);
        }

        if ($this->payment_status) {
            $query->where('payment_status', $this->payment_status);
        }

        if ($this->transmit_status) {
            $query->where('transmit', $this->transmit_status);
        }

        if ($this->currency) {
            $query->where('document_currency_code', $this->currency);
        }

        if ($this->date_from) {
            $query->whereDate('issue_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('issue_date', '<=', $this->date_to);
        }

        if ($this->amount_min || $this->amount_max) {
            $query->whereRaw("JSON_EXTRACT(legal_monetary_total, '$.payable_amount') IS NOT NULL");

            if ($this->amount_min) {
                $query->whereRaw("JSON_EXTRACT(legal_monetary_total, '$.payable_amount') >= ?", [$this->amount_min]);
            }

            if ($this->amount_max) {
                $query->whereRaw("JSON_EXTRACT(legal_monetary_total, '$.payable_amount') <= ?", [$this->amount_max]);
            }
        }

        $invoices = $query->latest()->paginate(10);

        $customers = Customer::orderBy('name')->get();
        $paymentStatuses = ['REJECTED', 'PENDING', 'PAID'];
        $transmitStatuses = ['PENDING', 'TRANSMITTING', 'TRANSMITTED', 'FAILED'];
        $currencies = ['NGN', 'USD', 'EUR', 'GBP', 'CAD', 'GHS'];

        return view('livewire.invoices.invoices-index', [
            'invoices' => $invoices,
            'customers' => $customers,
            'paymentStatuses' => $paymentStatuses,
            'transmitStatuses' => $transmitStatuses,
            'currencies' => $currencies,
        ]);
    }

    /**
     * View invoice details
     */
    public function viewInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Redirect to invoice details page or show modal
        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * Edit invoice
     */
    public function editInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Redirect to edit page
        return redirect()->route('invoices.edit', $invoice);
    }

    public function openUpdatePaymentModal($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $this->selectedInvoiceId = $invoice->id;
        $this->newPaymentStatus = $invoice->payment_status ?: 'PENDING';
    }

    public function updatePaymentStatus()
    {
        try {
            $invoice = Invoice::findOrFail($this->selectedInvoiceId);

            if (empty($invoice->irn)) {
                $this->dispatch('error', 'This invoice does not have a valid IRN, so payment cannot be updated on FIRS.');
                return;
            }

            $allowedStatuses = ['REJECTED', 'PENDING', 'PAID'];

            if (! in_array($this->newPaymentStatus, $allowedStatuses, true)) {
                $this->dispatch('error', 'Invalid payment status selected.');
                return;
            }

            $taxlyTenantId = Setting::getValue('taxly_tenant_id');
            $cred = TaxlyCredential::withoutGlobalScopes()->where('tenant_id', $taxlyTenantId)->first();

            if (! $cred) {
                $this->dispatch('error', 'Taxly credentials not configured. Please complete Taxly setup first.');
                return;
            }

            Log::info('Updating invoice payment status on Taxly', [
                'invoice_id' => $invoice->id,
                'invoice_reference' => $invoice->invoice_reference,
                'irn' => $invoice->irn,
                'payment_status' => $this->newPaymentStatus,
                'taxly_tenant_id' => $taxlyTenantId,
                'credential_id' => $cred->id,
            ]);

            $updatedStatus = $this->newPaymentStatus;

            $service = new TaxlyService($cred);
            $response = $service->updateInvoicePayment($invoice->irn, $updatedStatus);

            $invoice->transmissions()->create([
                'irn' => $invoice->irn,
                'action' => 'update_payment',
                'request_payload' => [
                    'payment_status' => $updatedStatus,
                ],
                'response_payload' => $response,
                'status' => 'success',
                'message' => 'Payment status updated successfully on FIRS.',
                'transmitted_at' => now(),
            ]);

            $invoice->update([
                'payment_status' => $updatedStatus,
            ]);

            $this->selectedInvoiceId = null;
            $this->newPaymentStatus = 'PENDING';
            $this->dispatch('modal-close', name: 'update-payment-status');
            $this->dispatch('success', "Invoice {$invoice->invoice_reference} payment status was updated to {$updatedStatus}.");
        } catch (\Throwable $e) {
            if ($this->selectedInvoiceId) {
                try {
                    $invoice = Invoice::find($this->selectedInvoiceId);

                    if ($invoice) {
                        $invoice->transmissions()->create([
                            'irn' => $invoice->irn,
                            'action' => 'update_payment',
                            'request_payload' => [
                                'payment_status' => $this->newPaymentStatus,
                            ],
                            'response_payload' => ['error' => $e->getMessage()],
                            'status' => 'failed',
                            'message' => 'Payment status update failed.',
                            'error' => $e->getMessage(),
                            'transmitted_at' => now(),
                        ]);
                    }
                } catch (\Throwable $inner) {
                    Log::error('Failed to record payment status update error', [
                        'invoice_id' => $this->selectedInvoiceId,
                        'error' => $inner->getMessage(),
                    ]);
                }
            }

            $this->dispatch('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }

    /**
     * Transmit invoice to Taxly
     */
    public function transmitInvoice($invoiceId)
    {
        try {
            $invoice = Invoice::with(['customer', 'organization'])->findOrFail($invoiceId);

            Log::info('Initiating async invoice transmission', [
                'invoice_id' => $invoice->id,
                'irn' => $invoice->irn,
            ]);

            // ✅ Ensure invoice has an IRN
            if (empty($invoice->irn)) {
                $this->dispatch('error', 'Invoice cannot be transmitted because it has no IRN.');
                return;
            }

            // ✅ Prevent duplicate transmission requests
            if ($invoice->transmit === 'TRANSMITTING') {
                $this->dispatch('error', 'This invoice is currently being transmitted.');
                return;
            }

            // ✅ Allow retry for FAILED or PENDING status
            if ($invoice->transmit === 'TRANSMITTED') {
                $this->dispatch('error', 'This invoice has already been successfully transmitted.');
                return;
            }

            // ✅ Initialize TaxlyService using Taxly tenant_id from settings
            $taxlyTenantId = Setting::getValue('taxly_tenant_id');
            // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
            $cred = TaxlyCredential::withoutGlobalScopes()->where('tenant_id', $taxlyTenantId)->first();
            $taxly = new TaxlyService($cred);

            $webhookUrl = route('taxly.webhook.invoice');
            Log::info('Using webhook URL for transmission', [
                'webhook_url' => $webhookUrl,
            ]);

            // ✅ Call transmitByIrn
            $response = $taxly->transmitByIrn($invoice->irn, $webhookUrl);

            // ✅ Change invoice transmit status to TRANSMITTING
            if ($response['code'] === 200 || ($response['data']['ok'] ?? true)) {
                $invoice->update([
                    'transmit' => 'TRANSMITTING',
                    'metadata' => array_merge($invoice->metadata ?? [], [
                        'transmission_initiated_at' => now()->toDateTimeString(),
                        'transmission_message' => $response['message'] ?? 'Transmission initiated',
                        'retry_count' => ($invoice->metadata['retry_count'] ?? 0) + 1,
                    ]),
                ]);
            }

            // ✅ Log transmission initiation
            $invoice->transmissions()->create([
                'action' => 'transmit',
                'request_payload' => ['irn' => $invoice->irn],
                'response_payload' => $response,
                'status' => 'initiated',
            ]);

            Log::info('Invoice transmission initiated', [
                'invoice_id' => $invoice->id,
                'response' => $response,
            ]);

            $this->dispatch('success', $response['message'] ?? 'Invoice transmission started.');
        } catch (\Throwable $e) {
            Log::error('Invoice transmission initiation failed', [
                'invoice_id' => $invoiceId ?? null,
                'error' => $e->getMessage(),
            ]);

            if (isset($invoice)) {
                try {
                    $invoice->transmissions()->create([
                        'action' => 'transmit',
                        'request_payload' => ['irn' => $invoice->irn ?? null],
                        'response_payload' => ['error' => $e->getMessage()],
                        'status' => 'failure',
                    ]);
                } catch (\Throwable $inner) {
                    Log::error('Failed to record transmission initiation error', [
                        'invoice_id' => $invoice->id ?? null,
                        'error' => $inner->getMessage(),
                    ]);
                }
            }

            $this->dispatch('error', 'Failed to initiate invoice transmission: ' . $e->getMessage());
        }
    }

    /**
     * Retry failed invoice transmission
     */
    public function retryTransmission($invoiceId)
    {
        try {
            $invoice = Invoice::with(['customer', 'organization'])->findOrFail($invoiceId);

            Log::info('Retrying invoice transmission', [
                'invoice_id' => $invoice->id,
                'irn' => $invoice->irn,
                'current_status' => $invoice->transmit,
                'retry_count' => $invoice->metadata['retry_count'] ?? 0,
            ]);

            // ✅ Ensure invoice has an IRN
            if (empty($invoice->irn)) {
                $this->dispatch('error', 'Invoice cannot be transmitted because it has no IRN.');
                return;
            }

            // ✅ Only allow retry for FAILED status
            if ($invoice->transmit !== 'FAILED') {
                $this->dispatch('error', 'Only failed transmissions can be retried.');
                return;
            }

            // ✅ Initialize TaxlyService using Taxly tenant_id from settings
            $taxlyTenantId = Setting::getValue('taxly_tenant_id');
            // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
            $cred = TaxlyCredential::withoutGlobalScopes()->where('tenant_id', $taxlyTenantId)->first();
            $taxly = new TaxlyService($cred);

            $webhookUrl = route('taxly.webhook.invoice');
            Log::info('Using webhook URL for retry transmission', [
                'webhook_url' => $webhookUrl,
            ]);

            // ✅ Call transmitByIrn for retry
            $response = $taxly->transmitByIrn($invoice->irn, $webhookUrl);

            // ✅ Change invoice transmit status to TRANSMITTING
            if ($response['code'] === 200 || ($response['data']['ok'] ?? true)) {
                $invoice->update([
                    'transmit' => 'TRANSMITTING',
                    'metadata' => array_merge($invoice->metadata ?? [], [
                        'transmission_initiated_at' => now()->toDateTimeString(),
                        'transmission_message' => $response['message'] ?? 'Retry transmission initiated',
                        'retry_count' => ($invoice->metadata['retry_count'] ?? 0) + 1,
                        'last_retry_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            // ✅ Log retry transmission initiation
            $invoice->transmissions()->create([
                'action' => 'retry_transmit',
                'request_payload' => ['irn' => $invoice->irn, 'retry' => true],
                'response_payload' => $response,
                'status' => 'initiated',
            ]);

            Log::info('Invoice retry transmission initiated', [
                'invoice_id' => $invoice->id,
                'response' => $response,
                'retry_count' => $invoice->metadata['retry_count'] ?? 1,
            ]);

            $this->dispatch('success', $response['message'] ?? 'Invoice retry transmission started.');
        } catch (\Throwable $e) {
            Log::error('Invoice retry transmission failed', [
                'invoice_id' => $invoiceId ?? null,
                'error' => $e->getMessage(),
            ]);

            if (isset($invoice)) {
                try {
                    $invoice->transmissions()->create([
                        'action' => 'retry_transmit',
                        'request_payload' => ['irn' => $invoice->irn ?? null, 'retry' => true],
                        'response_payload' => ['error' => $e->getMessage()],
                        'status' => 'failure',
                    ]);
                } catch (\Throwable $inner) {
                    Log::error('Failed to record retry transmission error', [
                        'invoice_id' => $invoice->id ?? null,
                        'error' => $inner->getMessage(),
                    ]);
                }
            }

            $this->dispatch('error', 'Failed to retry invoice transmission: ' . $e->getMessage());
        }
    }

    /**
     * Submit draft invoice to FIRS
     */
    public function submitToFIRS($invoiceId)
    {
        try {
            $invoice = Invoice::with(['customer', 'organization', 'lines'])->findOrFail($invoiceId);

            // Only allow submission for DRAFT invoices
            if ($invoice->transmit !== 'DRAFT') {
                $this->dispatch('error', 'Only draft invoices can be submitted to FIRS.');
                return;
            }

            Log::info('Submitting draft invoice to FIRS', [
                'invoice_id' => $invoice->id,
                'invoice_reference' => $invoice->invoice_reference,
            ]);

            // Use legal_monetary_total as-is (numbers, not strings)
            $legalMonetaryTotal = $invoice->legal_monetary_total ?? [];

            // Ensure all values are numeric (float), not strings
            $monetaryFields = ['tax_exclusive_amount', 'tax_inclusive_amount', 'line_extension_amount', 'payable_amount', 'charge_total_amount', 'allowance_total_amount', 'prepaid_amount'];
            foreach ($monetaryFields as $field) {
                $value = $legalMonetaryTotal[$field] ?? 0;
                $legalMonetaryTotal[$field] = (float) $value;
            }

            Log::debug('Legal monetary total after conversion', [
                'original' => $invoice->legal_monetary_total,
                'converted' => $legalMonetaryTotal,
            ]);

            // Build payload for Taxly submission
            $payload = [
                'channel' => 'api',
                'business_id' => $invoice->organization->business_id ?? null,
                'invoice_reference' => $invoice->invoice_reference,
                'irn' => $invoice->irn,
                'issue_date' => $invoice->issue_date->format('Y-m-d'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'issue_time' => now()->format('H:i:s'),
                'invoice_type_code' => $invoice->invoice_type_code,
                'document_currency_code' => $invoice->document_currency_code,
                'tax_currency_code' => $invoice->document_currency_code,
                'payment_status' => $invoice->payment_status,
                'accounting_supplier_party' => $invoice->accounting_supplier_party,
                'legal_monetary_total' => $legalMonetaryTotal,
                'invoice_line' => $this->formatInvoiceLinesForTaxly($invoice->lines),
                'payment_means' => [
                    [
                        'payment_means_code' => '10',
                        'payment_due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : now()->addDays(30)->format('Y-m-d'),
                    ],
                ],
                'tax_total' => $this->buildTaxTotal($invoice, $legalMonetaryTotal),
            ];

            // Only include customer party if customer is selected
            if ($invoice->customer_id || !empty($invoice->accounting_customer_party['party_name'])) {
                $payload['accounting_customer_party'] = $invoice->accounting_customer_party;
            }

            // Call Taxly service to submit to FIRS using Taxly tenant_id from settings
            $taxlyTenantId = Setting::getValue('taxly_tenant_id');
            // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
            $cred = TaxlyCredential::withoutGlobalScopes()->where('tenant_id', $taxlyTenantId)->first();
            $taxly = new TaxlyService($cred);

            // Submit invoice to FIRS
            $response = $taxly->submitInvoice($payload);

            Log::info('Draft invoice submitted to FIRS', [
                'invoice_id' => $invoice->id,
                'response' => $response,
            ]);

            // Update invoice status to PENDING
            $invoice->update([
                'transmit' => 'PENDING',
            ]);

            // Record successful submission
            $invoice->transmissions()->create([
                'action' => 'submit',
                'request_payload' => $payload,
                'response_payload' => $response,
                'status' => 'PENDING'
            ]);

            $this->dispatch('success', 'Invoice submitted to FIRS successfully. Ready for transmission.');
        } catch (\Throwable $e) {
            Log::error('Draft invoice submission to FIRS failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            if (isset($invoice)) {
                try {
                    $invoice->transmissions()->create([
                        'action' => 'submit',
                        'request_payload' => $payload ?? null,
                        'response_payload' => ['error' => $e->getMessage()],
                        'status' => 'failure'
                    ]);
                } catch (\Throwable $inner) {
                    Log::error('Failed to record submission error', [
                        'invoice_id' => $invoice->id ?? null,
                        'error' => $inner->getMessage(),
                    ]);
                }
            }

            $this->dispatch('error', 'Failed to submit invoice to FIRS: ' . $e->getMessage());
        }
    }

    /**
     * Format invoice lines for Taxly API
     */
    private function formatInvoiceLinesForTaxly($lines)
    {
        return $lines->map(function ($line, $index) {
            return [
                'hsn_code' => $line->hsn_code ?? 'GENERAL',
                'product_category' => $line->product_category ?? 'General Items',
                'invoiced_quantity' => (float) ($line->invoiced_quantity ?? 0),
                'line_extension_amount' => (float) (($line->price['price_amount'] ?? 0) * ($line->invoiced_quantity ?? 0)),
                'item' => $line->item ?? ['name' => 'Item', 'description' => 'Item description'],
                'price' => [
                    'price_amount' => (float) ($line->price['price_amount'] ?? 0),
                    'base_quantity' => (float) ($line->price['base_quantity'] ?? 1),
                    'price_unit' => $line->price['price_unit'] ?? 'NGN per 1',
                ],
                'order' => $index,
            ];
        })->toArray();
    }

    /**
     * Build tax total from invoice data
     */
    private function buildTaxTotal($invoice, $legalMonetaryTotal)
    {
        $taxTotals = [];

        // Get tax totals from invoice
        if ($invoice->taxTotals && $invoice->taxTotals->count() > 0) {
            foreach ($invoice->taxTotals as $taxTotal) {
                $taxTotals[] = [
                    'tax_amount' => (float) ($taxTotal->tax_amount ?? 0),
                    'tax_subtotal' => $this->formatTaxSubtotal($taxTotal->tax_subtotal ?? []),
                ];
            }
        } else {
            // Fallback to basic tax calculation
            $payableAmount = (float) ($legalMonetaryTotal['payable_amount'] ?? 0);
            $taxableAmount = (float) ($legalMonetaryTotal['tax_exclusive_amount'] ?? $payableAmount);
            $taxAmount = $payableAmount - $taxableAmount;

            $taxTotals[] = [
                'tax_amount' => (float) $taxAmount,
                'tax_subtotal' => [
                    [
                        'taxable_amount' => (float) $taxableAmount,
                        'tax_amount' => (float) $taxAmount,
                        'tax_category' => [
                            'id' => 'LOCAL_SALES_TAX',
                            'percent' => 7.5,
                        ],
                    ],
                ],
            ];
        }

        return $taxTotals;
    }

    /**
     * Format tax subtotal for Taxly API
     */
    private function formatTaxSubtotal($subtotals)
    {
        if (empty($subtotals)) {
            return [];
        }

        $formatted = [];
        foreach ($subtotals as $sub) {
            // Map stored fields to API expected format
            // Stored: tax_category_id (id), tax_percentage (percent)
            // API expects: tax_category.id, tax_category.percent
            $taxCategory = [
                'id' => $sub['tax_category_id'] ?? 'LOCAL_SALES_TAX',
                'percent' => (float) ($sub['tax_percentage'] ?? 7.5),
            ];

            $formatted[] = [
                'taxable_amount' => (float) ($sub['taxable_amount'] ?? 0),
                'tax_amount' => (float) ($sub['tax_amount'] ?? 0),
                'tax_category' => $taxCategory,
            ];
        }
        return $formatted;
    }

    /**
     * Delete invoice (Cancel)
     */
    public function deleteInvoice($invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            // Check if invoice can be deleted
            if (in_array($invoice->transmit, ['TRANSMITTING', 'TRANSMITTED', 'FAILED'], true)) {
                $this->dispatch('error', 'Cannot delete an invoice that has been transmitted to Taxly.');
                return;
            }

            DB::beginTransaction();

            // Delete related data
            $invoice->lines()->delete();
            $invoice->transmissions()->delete();
            $invoice->delete();

            DB::commit();

            // Show success message as plain payload to ensure toast renders text
            $this->dispatch('success', 'Invoice cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice deletion failed', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
            $this->dispatch('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }
}
