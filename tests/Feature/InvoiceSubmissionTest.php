<?php

namespace Tests\Feature;

use App\Jobs\SubmitInvoiceJob;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TaxlyCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InvoiceSubmissionTest extends TestCase
{
  use RefreshDatabase;

  public function test_invoice_submission_job_calls_taxly_api()
  {
    Http::fake([
      'taxly.ng/*' => Http::response(['status' => 'ok', 'irn' => 'INV001'], 200)
    ]);

    $tenant = Tenant::factory()->create();
    $cred = TaxlyCredential::factory()->create([
      'tenant_id' => $tenant->id,
      'api_key' => 'demo'
    ]);

    $invoice = Invoice::factory()->create(['tenant_id' => $tenant->id]);
    dispatch(new SubmitInvoiceJob($invoice));

    $this->assertDatabaseHas('invoice_transmissions', [
      'invoice_id' => $invoice->id,
      'status' => 'success'
    ]);
  }
}
