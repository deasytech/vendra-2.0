<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{
  Tenant,
  Organization,
  Customer,
  TaxlyCredential,
  Invoice,
  InvoiceLine,
  InvoiceTaxTotal,
  InvoiceTransmission,
  InvoiceAttachment
};
use App\Services\PdfInvoiceService;

class DemoDataSeeder extends Seeder
{
  public function run(): void
  {
    Tenant::factory(3)->create()->each(function ($tenant) {
      $organization = Organization::factory()->create(['tenant_id' => $tenant->id]);
      $customer = Customer::factory()->create();

      TaxlyCredential::factory()->create(['tenant_id' => $tenant->id]);

      Invoice::factory(5)->create([
        'tenant_id' => $tenant->id,
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
      ])->each(function ($invoice) {
        InvoiceLine::factory(3)->create(['invoice_id' => $invoice->id]);
        InvoiceTaxTotal::factory()->create(['invoice_id' => $invoice->id]);
        InvoiceTransmission::factory()->create(['invoice_id' => $invoice->id]);

        $path = PdfInvoiceService::generate($invoice);

        // InvoiceAttachment::factory()->create([
        //   'invoice_id' => $invoice->id,
        //   'path' => $path,
        //   'filename' => basename($path),
        //   'mime' => 'application/pdf',
        // ]);
      });
    });
  }
}
