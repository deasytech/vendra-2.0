<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\TaxlyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SubmitInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function handle(): void
    {
        $service = new TaxlyService($this->invoice->tenant->taxlyCredential);

        $payload = [
            'business_id' => $this->invoice->business_id,
            'invoice_reference' => $this->invoice->invoice_reference,
            'irn' => $this->invoice->irn,
            'invoice_line' => $this->invoice->lines,
        ];

        try {
            $response = $service->submitInvoice($payload);
            $this->invoice->transmissions()->create([
                'action' => 'submit-job',
                'request_payload' => $payload,
                'response_payload' => $response,
                'status' => 'success'
            ]);
        } catch (Throwable $e) {
            $this->invoice->transmissions()->create([
                'action' => 'submit-job',
                'response_payload' => ['error' => $e->getMessage()],
                'status' => 'failure'
            ]);
            throw $e;
        }
    }
}
