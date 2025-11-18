<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Services\FirsQrService;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;
    public $encrypted;
    public $qrDataUri;

    public function mount(Invoice $invoice, FirsQrService $qrService)
    {
        $this->invoice = $invoice->load(['lines', 'organization', 'customer', 'taxTotals']);

        if ($invoice->irn) {
            $this->encrypted = $qrService->generateEncryptedQrPayload($invoice->irn);
            $this->qrDataUri = $this->generateQrCode($this->encrypted);
        }
    }

    private function generateQrCode(string $data): string
    {
        // Use simple QR code generation without external library dependency
        $size = 200;
        $margin = 10;

        // Create a simple data URI for QR code placeholder
        // In production, you might want to use a proper QR code library
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
    <rect width="{$size}" height="{$size}" fill="white"/>
    <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-family="monospace" font-size="10" fill="black">
        QR: {$data}
    </text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function render()
    {
        return view('livewire.invoices.invoice-show');
    }
}
