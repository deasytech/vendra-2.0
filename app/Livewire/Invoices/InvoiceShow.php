<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\TaxlyCredential;
use App\Services\FirsQrService;
use App\Services\TaxlyInvoicePayloadBuilder;
use App\Services\TaxlyService;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;
    public $encrypted;
    public $qrDataUri;
    public $invoiceId;
    public $isTransmitting = false;

    public function mount(Invoice $invoice, FirsQrService $qrService)
    {
        $this->invoice = $invoice->load(['lines', 'organization', 'customer', 'taxTotals', 'transmissions', 'attachments']);

        $this->invoiceId = $invoice->id;

        if ($invoice->irn) {
            $this->encrypted = $qrService->generateEncryptedQrPayload($invoice->irn);
            $this->qrDataUri = $this->generateQrCode($this->encrypted);
        }
    }

    private function generateQrCode(string $data): string
    {
        try {
            // Create QR code renderer with proper styling
            $renderer = new ImageRenderer(
                new RendererStyle(200, margin: 10), // 200px size with 10px margin
                new ImagickImageBackEnd()
            );

            $writer = new Writer($renderer);

            // Generate QR code as base64 PNG
            $qrCode = $writer->writeString($data);

            return 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            // Fallback to SVG if ImageMagick is not available
            return $this->generateFallbackQrCode($data);
        }
    }

    private function generateFallbackQrCode(string $data): string
    {
        // Simple SVG QR code fallback
        $size = 200;
        $moduleSize = 4; // Size of each QR module
        $modules = 25; // Number of modules (simplified)

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';

        // Create a simple pattern (this is a simplified QR representation)
        // In a real implementation, you'd want to use the actual QR algorithm
        for ($row = 0; $row < $modules; $row++) {
            for ($col = 0; $col < $modules; $col++) {
                // Simple pattern generation based on data hash
                $hash = crc32($data . $row . $col);
                if ($hash % 2 == 0) {
                    $x = ($col * $moduleSize) + 10;
                    $y = ($row * $moduleSize) + 10;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $moduleSize . '" height="' . $moduleSize . '" fill="black"/>';
                }
            }
        }

        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function transmitInvoice()
    {
        $this->isTransmitting = true;

        try {
            $invoice = Invoice::with(['customer', 'organization'])->findOrFail($this->invoiceId);

            Log::info('Initiating async invoice transmission', [
                'invoice_id' => $invoice->id,
                'irn' => $invoice->irn,
            ]);

            // ✅ Ensure invoice has an IRN
            if (empty($invoice->irn)) {
                $this->dispatch('error', 'Invoice cannot be transmitted because it has no IRN.');
                $this->isTransmitting = false;
                return;
            }

            // ✅ Prevent duplicate transmission requests
            if ($invoice->transmit !== 'PENDING') {
                $this->dispatch('error', 'This invoice has already been transmitted.');
                $this->isTransmitting = false;
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
            $this->isTransmitting = false;

            // Refresh the invoice data to show updated transmission status
            $this->invoice->refresh();
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
            $this->isTransmitting = false;
        }
    }

    public function render()
    {
        return view('livewire.invoices.invoice-show');
    }
}
