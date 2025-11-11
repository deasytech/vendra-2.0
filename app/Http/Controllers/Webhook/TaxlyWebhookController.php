<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceTransmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaxlyWebhookController extends Controller
{
  public function handle(Request $request)
  {
    Log::info('Taxly webhook received', [
      'headers' => $request->headers->all(),
      'payload' => $request->all()
    ]);

    // Handle transmission webhook response from transmitByIrn
    $irn = $request->input('irn');
    $status = $request->input('status');
    $transmissionId = $request->input('transmission_id');
    $message = $request->input('message');
    $error = $request->input('error');
    $transmittedAt = $request->input('transmitted_at');

    if ($irn) {
      $invoice = Invoice::where('irn', $irn)->first();

      if ($invoice) {
        // Handle different webhook status updates
        if ($status === 'transmitting') {
          // Intermediate status: transmission has started but not completed
          $invoice->update(['transmit' => 'TRANSMITTING']);
        } else {
          // Final status: transmission completed (success or failed)
          $transmitStatus = $status === 'success' ? 'TRANSMITTED' : 'FAILED';
          $invoice->update(['transmit' => $transmitStatus]);
        }

        // Create or update transmission record
        $transmissionData = [
          'invoice_id' => $invoice->id,
          'irn' => $irn,
          'action' => 'transmitted', // Set the action for transmission webhook
          'status' => $status,
          'message' => $message,
          'error' => $error,
          'transmitted_at' => $transmittedAt ? now()->parse($transmittedAt) : now(),
        ];

        if ($transmissionId) {
          $transmissionData['id'] = $transmissionId;
        }

        InvoiceTransmission::updateOrCreate(
          [
            'invoice_id' => $invoice->id,
            'irn' => $irn
          ],
          $transmissionData
        );

        Log::info('Invoice transmission updated', [
          'irn' => $irn,
          'status' => $status,
          'invoice_id' => $invoice->id
        ]);
      } else {
        Log::warning('Invoice not found for IRN', ['irn' => $irn]);
      }
    } else {
      Log::warning('No IRN provided in webhook payload');
    }

    return response()->json(['status' => 'success', 'message' => 'Webhook processed']);
  }
}
