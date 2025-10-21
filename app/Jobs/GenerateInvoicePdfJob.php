<?php

namespace App\Jobs;

use App\Models\{Invoice, InvoiceAttachment};
use App\Services\PdfInvoiceService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdfJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public int $tries = 3;

  public function __construct(public int $invoiceId, public ?int $userId = null) {}

  public function handle(): void
  {
    try {
      $invoice = Invoice::with(['attachments', 'organization', 'business', 'lines'])->findOrFail($this->invoiceId);

      // Generate the PDF file
      $path = PdfInvoiceService::generate($invoice);

      // Remove any old attachments
      foreach ($invoice->attachments as $attachment) {
        if (Storage::disk('public')->exists($attachment->path)) {
          Storage::disk('public')->delete($attachment->path);
        }
        $attachment->delete();
      }

      // Store the new attachment record
      InvoiceAttachment::create([
        'invoice_id' => $invoice->id,
        'path' => $path,
        'filename' => basename($path),
        'mime' => 'application/pdf',
        'meta' => [
          'description' => 'Async regenerated invoice PDF',
          'size_kb' => round(Storage::disk('public')->size($path) / 1024, 2),
          'generated_at' => now()->toDateTimeString(),
        ],
      ]);

      // Notify success (if Filament user context exists)
      if ($this->userId) {
        Notification::make()
          ->title('Invoice PDF regenerated successfully')
          ->success()
          ->sendToDatabase(Auth::user());
      }

      Log::info('Invoice PDF generated successfully', ['invoice_id' => $invoice->id]);
    } catch (\Throwable $e) {
      Log::error('Failed to generate invoice PDF', [
        'invoice_id' => $this->invoiceId,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      // Retry automatically up to 3 times (handled by $tries)
      if ($this->attempts() >= $this->tries) {
        Notification::make()
          ->title('Invoice PDF generation failed after multiple attempts')
          ->danger()
          ->body($e->getMessage())
          ->sendToDatabase(Auth::user());
      }
    }
  }
}
