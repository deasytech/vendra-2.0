<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfInvoiceService
{
  public static function generate(Invoice $invoice): string
  {
    $filename = 'invoice-' . Str::slug($invoice->invoice_reference) . '.pdf';
    $path = 'invoices/' . $filename;

    $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
    Storage::disk('public')->put($path, $pdf->output());

    return $path;
  }
}
