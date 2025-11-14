<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Services\TaxlyService;
use App\Models\TaxlyCredential;
use App\Jobs\SubmitInvoiceJob;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicesIndex extends Component
{
    public function render()
    {
        $invoices = Invoice::with(['customer', 'organization'])->latest()->paginate(10);

        return view('livewire.invoices.invoices-index', [
            'invoices' => $invoices,
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
     * Delete invoice
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

            $this->dispatch('success', message: 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice deletion failed', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
            $this->dispatch('error', message: 'Failed to delete invoice: ' . $e->getMessage());
        }
    }
}
