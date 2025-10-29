<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Services\TaxlyService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TransmittedInvoices extends Component
{
    public function confirmInvoice($invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            if (!$invoice->irn) {
                $this->dispatch('error', 'Invoice does not have an IRN number');
                return;
            }

            $payload = [
                'agent_id' => $invoice->business->tin ?? '01060087-0001',
                'base_amount' => $invoice->legal_monetary_total['tax_exclusive_amount'] ?? 0,
                'beneficiary_tin' => $invoice->business->tin ?? '01060087-0001',
                'currency' => $invoice->document_currency_code ?? 'NGN',
                'item_description' => $invoice->note ?? 'Invoice Confirmation',
                'irn' => $invoice->irn,
                'other_taxes' => $invoice->taxTotals->sum('tax_amount') ?? 0,
                'total_amount' => $invoice->legal_monetary_total['tax_inclusive_amount'] ?? 0,
                'transaction_date' => $invoice->issue_date ?? now()->format('Y-m-d'),
                'integrator_service_id' => config('services.taxly.integrator_service_id'),
                'vat_calculated' => $invoice->taxTotals->where('tax_category_id', 'VAT')->sum('tax_amount') ?? 0,
                'vat_rate' => 7.5,
                'vat_status' => 'STANDARD_VAT',
            ];
            Log::info('Confirming invoice with payload', ['payload' => $payload]);
            $taxlyService = new TaxlyService();
            $result = $taxlyService->confirmTransmittingInvoice($invoice->irn, $payload);
            Log::info('Invoice confirmation result', ['result' => $result]);
            // Log the confirmation result in your transmissions table
            $invoice->transmissions()->create([
                'action' => 'confirm',
                'request_payload' => $payload,
                'response_payload' => $result,
                'status' => ($result['code'] ?? 500) === 200 ? 'success' : 'failed',
            ]);

            if (($result['code'] ?? 500) === 200) {
                // Update invoice status to TRANSMITTED on successful confirmation
                $invoice->update(['transmit' => 'TRANSMITTED']);
                $this->dispatch('success', 'Invoice confirmed successfully');
            } else {
                $this->dispatch('error', 'Invoice confirmation failed on FIRS');
            }
        } catch (\Exception $e) {
            Log::error('Invoice confirmation failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('error', 'Failed to confirm invoice: ' . $e->getMessage());
        }
    }

    public function downloadInvoice($invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            if (!$invoice->irn) {
                $this->dispatch('error', 'Invoice does not have an IRN number');
                return;
            }

            $taxlyService = new TaxlyService();
            $result = $taxlyService->downloadByIrn($invoice->irn);

            // Log the download action
            $invoice->transmissions()->create([
                'action' => 'download',
                'request_payload' => ['irn' => $invoice->irn],
                'response_payload' => $result,
                'status' => 'success'
            ]);

            // Redirect to the download URL if present
            if (isset($result['download_url'])) {
                return redirect()->away($result['download_url']);
            } elseif (isset($result['data']['download_url'])) {
                return redirect()->away($result['data']['download_url']);
            } else {
                $this->dispatch('error', 'Download URL not available in response');
            }
        } catch (\Exception $e) {
            Log::error('Invoice download failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('error', 'Failed to download invoice: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $transmittedInvoices = Invoice::with(['transmissions', 'customer', 'organization'])
            ->where('transmit', '!=', 'PENDING')
            ->latest()
            ->paginate(10);

        return view('livewire.invoices.transmitted-invoices', [
            'invoices' => $transmittedInvoices
        ]);
    }
}
