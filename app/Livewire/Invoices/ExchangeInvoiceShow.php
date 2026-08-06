<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ExchangeInvoiceShow extends Component
{
  public Invoice $invoice;
  public $qrDataUri = null;

  public function mount(Invoice $invoice)
  {
    $this->invoice = $invoice;

    // Ensure this is an exchange invoice
    if (data_get($this->invoice->metadata, 'direction') !== 'INCOMING') {
      abort(404, 'This is not an exchange invoice');
    }

    // Ensure user has access to this invoice
    if ($this->invoice->tenant_id !== Auth::user()->tenant_id) {
      abort(403, 'Unauthorized access');
    }

    // Generate QR code data URI if IRN exists
    if ($this->invoice->irn) {
      try {
        $qrService = app(\App\Services\FirsQrService::class);
        $encrypted = $qrService->generateEncryptedQrPayload($this->invoice->irn);
        $this->qrDataUri = $this->generateQrCode($encrypted);
      } catch (\Exception $e) {
        // If QR generation fails, continue without it
        $this->qrDataUri = null;
      }
    }
  }

  public function render()
  {
    Log::info('Rendering exchange invoice show component', [
      'invoice_id' => $this->invoice->id,
      'has_irn' => !empty($this->invoice->irn),
      'qr_data_uri_generated' => !empty($this->qrDataUri),
    ]);
    return view('livewire.invoices.exchange-invoice-show', [
      'invoice' => $this->invoice,
      'qrDataUri' => $this->qrDataUri,
    ]);
  }

  public function downloadInvoice()
  {
    try {
      $invoice = Invoice::with(['lines', 'organization', 'customer', 'taxTotals'])
        ->findOrFail($this->invoice->id);

      $filename = 'exchange-invoice-' . Str::slug($invoice->invoice_reference ?: ('ref-' . $invoice->id)) . '.pdf';

      $settingScope = [
        'tenant_id' => $invoice->tenant_id,
        'organization_id' => $invoice->organization_id,
        'user_id' => null,
      ];

      $pdf = Pdf::loadView('pdf.invoice', [
        'invoice' => $invoice,
        'qrDataUri' => $this->qrDataUri,
        'irn' => $invoice->irn,
        'settingScope' => $settingScope,
      ])->setPaper('a4', 'portrait');

      return response()->streamDownload(function () use ($pdf) {
        echo $pdf->output();
      }, $filename, [
        'Content-Type' => 'application/pdf',
      ]);
    } catch (\Throwable $e) {
      logger()->error('Exchange invoice PDF generation failed', [
        'invoice_id' => $this->invoice->id,
        'error' => $e->getMessage(),
      ]);

      session()->flash('error', 'Failed to generate PDF: ' . $e->getMessage());
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
    } catch (\Throwable $e) {
      Log::warning('Imagick QR code generation failed, using SVG fallback', ['error' => $e->getMessage()]);
      // Fallback to SVG if ImageMagick is not available
      return $this->generateFallbackQrCode($data);
    }
  }

  private function generateFallbackQrCode(string $data): string
  {
    try {
      // Use SvgImageBackEnd which does not require external extensions like Imagick or GD
      $renderer = new ImageRenderer(
        new RendererStyle(200, margin: 10),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
      );

      $writer = new Writer($renderer);
      $qrCode = $writer->writeString($data);

      return 'data:image/svg+xml;base64,' . base64_encode($qrCode);
    } catch (\Throwable $e) {
      Log::error('Fallback SVG QR code generation failed', ['error' => $e->getMessage()]);
      return '';
    }
  }
}
