<?php

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceCreate;
use App\Livewire\Invoices\InvoiceEdit;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\TaxlyCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TaxlyInvoicePayloadTest extends TestCase
{
  use RefreshDatabase;

  private function makeCredential(): void
  {
    $cred = new TaxlyCredential([
      'tenant_id' => null,
      'auth_type' => 'api_key',
      'api_key' => 'demo-key',
      'base_url' => 'https://taxly.ng',
    ]);
    $cred->save();
  }

  private function validateInvoicePayload(array $lines): array
  {
    Http::fake([
      'taxly.ng/*' => Http::response(['success' => true], 200),
    ]);

    Livewire::test(InvoiceCreate::class)
      ->set('invoice_reference', 'TEST-CAT-001')
      ->set('issue_date', now()->format('Y-m-d'))
      ->set('due_date', now()->addDays(30)->format('Y-m-d'))
      ->set('selected_customer_id', '') // no customer selected -> B2C
      ->set('customer.party_name', '')
      ->set('invoice_lines', $lines)
      ->call('validateInvoice');

    $recorded = Http::recorded();
    foreach ($recorded as [$request]) {
      if (str_contains($request->url(), '/invoices/validate')) {
        return $request->data();
      }
    }

    self::fail('validateInvoice request was not sent to /invoices/validate.');
  }

  public function test_b2c_validation_payload_includes_invoice_kind_and_non_null_product_category(): void
  {
    $this->makeCredential();

    $payload = $this->validateInvoicePayload([
      [
        'item' => ['name' => 'Unknown HS item', 'description' => 'desc'],
        'invoiced_quantity' => 1,
        'price' => ['price_amount' => 500.0, 'base_quantity' => 1, 'price_unit' => 'C62'],
        'selected_tax' => 'STANDARD_VAT',
        'hsn_code' => '0101.29', // code not present in the synced taxonomy
        'isic_code' => null,
        'product_category' => null,
        'service_category' => null,
      ],
    ]);

    // invoice_kind must always be present (previously omitted for B2C).
    self::assertSame('B2C', $payload['invoice_kind']);
    self::assertArrayNotHasKey('accounting_customer_party', $payload);

    // product_category must never be null when hsn_code is provided.
    self::assertSame('0101.29', $payload['invoice_line'][0]['hsn_code']);
    self::assertSame('0101.29', $payload['invoice_line'][0]['product_category']);
  }

  public function test_b2c_validation_payload_includes_non_null_service_category(): void
  {
    $this->makeCredential();

    $payload = $this->validateInvoicePayload([
      [
        'item' => ['name' => 'Unknown service', 'description' => 'desc'],
        'invoiced_quantity' => 1,
        'price' => ['price_amount' => 500.0, 'base_quantity' => 1, 'price_unit' => 'C62'],
        'selected_tax' => 'STANDARD_VAT',
        'hsn_code' => null,
        'isic_code' => '6312', // code not present in the synced taxonomy
        'product_category' => null,
        'service_category' => null,
      ],
    ]);

    // service_category must never be null when isic_code is provided.
    self::assertSame('6312', $payload['invoice_line'][0]['isic_code']);
    self::assertSame('6312', $payload['invoice_line'][0]['service_category']);
  }

  public function test_dropdown_selection_populates_category_from_the_selected_item_name(): void
  {
    $this->makeCredential();

    // Simulate what the Alpine picker does on select: it sets the code AND the
    // item's name (item.description from the API list) as the category.
    $component = Livewire::test(InvoiceCreate::class)
      ->set('invoice_reference', 'TEST-CAT-002')
      ->set('issue_date', now()->format('Y-m-d'))
      ->set('due_date', now()->addDays(30)->format('Y-m-d'))
      ->set('selected_customer_id', '')
      ->set('customer.party_name', '')
      ->set('invoice_lines', [
        [
          'item' => ['name' => 'HS Item', 'description' => 'desc'],
          'invoiced_quantity' => 1,
          'price' => ['price_amount' => 500.0, 'base_quantity' => 1, 'price_unit' => 'C62'],
          'selected_tax' => 'STANDARD_VAT',
          'hsn_code' => null,
          'isic_code' => null,
          'product_category' => null,
          'service_category' => null,
        ],
        [
          'item' => ['name' => 'Service Item', 'description' => 'desc'],
          'invoiced_quantity' => 1,
          'price' => ['price_amount' => 500.0, 'base_quantity' => 1, 'price_unit' => 'C62'],
          'selected_tax' => 'STANDARD_VAT',
          'hsn_code' => null,
          'isic_code' => null,
          'product_category' => null,
          'service_category' => null,
        ],
      ]);

    // HS item selected from the dropdown: code + name are both applied.
    $component->set('invoice_lines.0.hsn_code', '0101.29');
    $component->set('invoice_lines.0.product_category', 'Live horses');

    // Service item selected from the dropdown: code + name are both applied.
    $component->set('invoice_lines.1.isic_code', '6312');
    $component->set('invoice_lines.1.service_category', 'Web portals');

    Http::fake([
      'taxly.ng/*' => Http::response(['success' => true], 200),
    ]);

    $component->call('validateInvoice');

    $recorded = Http::recorded();
    foreach ($recorded as [$request]) {
      if (str_contains($request->url(), '/invoices/validate')) {
        $payload = $request->data();
        self::assertSame('0101.29', $payload['invoice_line'][0]['hsn_code']);
        self::assertSame('Live horses', $payload['invoice_line'][0]['product_category']);
        self::assertSame('6312', $payload['invoice_line'][1]['isic_code']);
        self::assertSame('Web portals', $payload['invoice_line'][1]['service_category']);
        return;
      }
    }

    self::fail('validateInvoice request was not sent to /invoices/validate.');
  }

  public function test_b2b_validation_payload_includes_invoice_kind_when_customer_is_selected(): void
  {
    $this->makeCredential();

    $customer = Customer::create([
      'name' => 'Acme Corp',
      'tin' => '12345678901',
      'email' => 'acme@example.com',
      'phone' => '+2348012345678',
    ]);

    Http::fake([
      'taxly.ng/*' => Http::response(['success' => true], 200),
    ]);

    Livewire::test(InvoiceCreate::class)
      ->set('invoice_reference', 'TEST-B2B-001')
      ->set('issue_date', now()->format('Y-m-d'))
      ->set('due_date', now()->addDays(30)->format('Y-m-d'))
      ->set('selected_customer_id', $customer->id)
      ->set('invoice_lines', [[
        'item' => ['name' => 'HS Item', 'description' => 'desc'],
        'invoiced_quantity' => 1,
        'price' => ['price_amount' => 500.0, 'base_quantity' => 1, 'price_unit' => 'C62'],
        'selected_tax' => 'STANDARD_VAT',
        'hsn_code' => '0101.29',
        'isic_code' => null,
        'product_category' => 'Live horses',
        'service_category' => null,
      ]])
      ->call('validateInvoice');

    $recorded = Http::recorded();
    foreach ($recorded as [$request]) {
      if (str_contains($request->url(), '/invoices/validate')) {
        $payload = $request->data();
        self::assertSame('B2B', $payload['invoice_kind']);
        self::assertSame('Acme Corp', $payload['accounting_customer_party']['party_name']);
        return;
      }
    }

    self::fail('validateInvoice request was not sent to /invoices/validate.');
  }

  public function test_edit_validation_payload_includes_invoice_kind_for_b2c(): void
  {
    $this->makeCredential();

    $invoice = Invoice::factory()->create([
      'tenant_id' => null,
      'organization_id' => null,
      'customer_id' => null,
      'invoice_reference' => 'EDIT-B2C-001',
      'accounting_customer_party' => [],
      'accounting_supplier_party' => [
        'party_name' => 'Supplier Ltd',
        'tin' => '17883307-0001',
      ],
    ]);

    InvoiceLine::factory()->create([
      'invoice_id' => $invoice->id,
      'hsn_code' => '0101.29',
      'isic_code' => null,
      'product_category' => 'Live horses',
      'service_category' => null,
    ]);

    Http::fake([
      'taxly.ng/*' => Http::response(['success' => true], 200),
    ]);

    Livewire::test(InvoiceEdit::class, ['invoice' => $invoice->load('lines')])
      ->call('validateInvoice');

    $recorded = Http::recorded();
    foreach ($recorded as [$request]) {
      if (str_contains($request->url(), '/invoices/validate')) {
        $payload = $request->data();
        self::assertSame('B2C', $payload['invoice_kind']);
        self::assertArrayNotHasKey('accounting_customer_party', $payload);
        return;
      }
    }

    self::fail('validateInvoice request was not sent to /invoices/validate.');
  }
}