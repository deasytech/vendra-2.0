<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\TaxlyCredential;
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
        // Get the Taxly tenant_id from settings (the one from Taxly integrator registration)
        $taxlyTenantId = Setting::getValue('taxly_tenant_id');
        // Use withoutGlobalScopes since Taxly tenant_id is external to our tenant system
        $credential = TaxlyCredential::withoutGlobalScopes()->where('tenant_id', $taxlyTenantId)->first();
        $service = new TaxlyService($credential);

        $payload = [
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
