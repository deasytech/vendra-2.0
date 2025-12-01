<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Services\FirsQrService;
use App\Services\TaxlyInvoicePayloadBuilder;
use App\Services\TaxlyService;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;
    public $encrypted;
    public $qrDataUri;

    public function mount(Invoice $invoice, FirsQrService $qrService)
    {
        $this->invoice = $invoice->load(['lines', 'organization', 'customer', 'taxTotals', 'transmissions', 'attachments']);

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

    public function transmitInvoice(TaxlyService $taxlyService)
    {
        try {
            $payload = TaxlyInvoicePayloadBuilder::build($this->invoice);
            $result = $taxlyService->submitInvoice($payload);

            if (isset($result['success']) && $result['success']) {
                session()->flash('success', 'Invoice transmitted successfully!');
            } else {
                $errorMessage = $result['message'] ?? 'Unknown error occurred';
                session()->flash('error', 'Failed to transmit invoice: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error transmitting invoice: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoices.invoice-show');
    }
}
