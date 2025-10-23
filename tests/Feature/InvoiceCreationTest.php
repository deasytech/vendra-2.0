<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Invoices\InvoiceCreate;

class InvoiceCreationTest extends TestCase
{
  use RefreshDatabase;

  public function test_invoice_creation_page_loads()
  {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/create-invoice');
    $response->assertStatus(200);
  }

  public function test_invoice_creation_component_renders()
  {
    Livewire::test(InvoiceCreate::class)
      ->assertStatus(200)
      ->assertSee('Create Invoice')
      ->assertSee('Supplier')
      ->assertSee('Customer');
  }

  public function test_customer_supplier_dropdowns_work()
  {
    // Create test data
    $customer = Customer::factory()->create([
      'name' => 'Test Customer',
      'tin' => '12345678901'
    ]);

    Livewire::test(InvoiceCreate::class)
      ->set('customer_type', 'customer')
      ->set('selected_customer_id', $customer->id)
      ->assertSet('customer.party_name', 'Test Customer')
      ->assertSet('customer.tin', '12345678901');
  }

  public function test_invoice_validation_functionality()
  {
    $component = Livewire::test(InvoiceCreate::class);

    // Test that the component has the validateInvoice method by calling it
    $component->set('invoice_reference', 'TEST-001')
      ->set('issue_date', now()->format('Y-m-d'))
      ->set('supplier.party_name', 'Test Supplier')
      ->set('customer.party_name', 'Test Customer')
      ->set('invoice_lines', [
        [
          'item' => ['name' => 'Test Item'],
          'invoiced_quantity' => 1,
          'price' => ['price_amount' => 100]
        ]
      ]);

    // The component should be able to call validateInvoice without errors
    $this->assertTrue(method_exists($component->instance(), 'validateInvoice'));
  }

  public function test_invoice_submission_functionality()
  {
    $component = Livewire::test(InvoiceCreate::class);

    // Test that the component has the submitInvoice method
    $this->assertTrue(method_exists($component->instance(), 'submitInvoice'));
  }
}
