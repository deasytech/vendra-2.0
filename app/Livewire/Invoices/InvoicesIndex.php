<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Customer;
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
        $query = Invoice::with(['customer', 'organization']);

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
        $paymentStatuses = ['paid', 'pending', 'overdue'];
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
            if ($invoice->transmit !== 'PENDING') {
                $this->dispatch('error', 'This invoice has already been transmitted.');
                return;
            }

            // ✅ Initialize TaxlyService
            $cred = TaxlyCredential::first();
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

            $this->dispatch('error', message: 'Failed to initiate invoice transmission: ' . $e->getMessage());
        }
    }

    /**
     * Delete invoice (Cancel)
     */
    public function deleteInvoice($invoiceId)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($invoiceId);

            // Check if invoice can be deleted
            if ($invoice->irn) {
                $this->dispatch('error', message: 'Cannot delete an invoice that has been transmitted to Taxly.');
                return;
            }

            // Delete related data
            $invoice->lines()->delete();
            $invoice->transmissions()->delete();
            $invoice->delete();

            DB::commit();

            // Close the modal and show success message
            $this->dispatch('close-modal', 'confirm-invoice-cancellation-' . $invoiceId);
            $this->dispatch('success', message: 'Invoice cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice deletion failed', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
            $this->dispatch('error', message: 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }
}
