<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    return view('livewire.invoices.exchange-invoice-show', [
      'invoice' => $this->invoice,
      'qrDataUri' => $this->qrDataUri,
    ]);
  }

  public function downloadInvoice()
  {
    try {
      // Generate PDF for exchange invoice
      $pdfPath = $this->generateExchangeInvoicePdf();

      return response()->download($pdfPath, 'exchange-invoice-' . $this->invoice->invoice_reference . '.pdf', [
        'Content-Type' => 'application/pdf',
      ])->deleteFileAfterSend(true);
    } catch (\Exception $e) {
      logger()->error('Exchange invoice PDF generation failed', [
        'invoice_id' => $this->invoice->id,
        'error' => $e->getMessage(),
      ]);

      session()->flash('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
  }

  private function generateExchangeInvoicePdf()
  {
    // For now, return a placeholder or use existing PDF generation
    // You can implement custom PDF generation for exchange invoices here
    $existingPdf = $this->invoice->attachments()
      ->where('file_name', 'like', '%.pdf')
      ->first();

    if ($existingPdf && Storage::exists($existingPdf->file_path)) {
      return Storage::path($existingPdf->file_path);
    }

    // Fallback: generate a simple PDF or throw exception
    throw new \Exception('PDF generation for exchange invoices not yet implemented');
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
