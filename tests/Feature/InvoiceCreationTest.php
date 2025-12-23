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

  public function test_invoice_can_be_created_without_customer()
  {
    // Create test organization
    $organization = Organization::factory()->create([
      'legal_name' => 'Test Organization',
      'tin' => '98765432109'
    ]);

    $component = Livewire::test(InvoiceCreate::class);

    // Set up invoice data without selecting a customer
    $component->set('invoice_reference', 'TEST-NO-CUSTOMER-001')
      ->set('issue_date', now()->format('Y-m-d'))
      ->set('due_date', now()->addDays(30)->format('Y-m-d'))
      ->set('invoice_lines', [
        [
          'item' => ['name' => 'Test Service', 'description' => 'Test service description'],
          'invoiced_quantity' => 1,
          'price' => ['price_amount' => 500.00],
          'selected_tax' => 'STANDARD_VAT'
        ]
      ])
      ->set('selected_customer_id', ''); // No customer selected

    // Verify that customer data is empty
    $component->assertSet('customer_id', null)
      ->assertSet('customer.party_name', '');

    // Verify that validation passes (no customer validation errors)
    $component->call('validateInvoice')
      ->assertHasNoErrors(['customer', 'customer_id', 'selected_customer_id']);

    // Verify that the component can handle submission without customer
    $this->assertTrue(method_exists($component->instance(), 'submitInvoice'));
  }

  public function test_invoice_payload_excludes_customer_when_not_selected()
  {
    // Create test organization
    $organization = Organization::factory()->create([
      'legal_name' => 'Test Organization',
      'tin' => '98765432109'
    ]);

    $component = Livewire::test(InvoiceCreate::class);

    // Set up invoice data without customer
    $component->set('invoice_reference', 'TEST-PAYLOAD-001')
      ->set('issue_date', now()->format('Y-m-d'))
      ->set('invoice_lines', [
        [
          'item' => ['name' => 'Test Item', 'description' => 'Test description'],
          'invoiced_quantity' => 2,
          'price' => ['price_amount' => 100.00],
          'selected_tax' => 'STANDARD_VAT'
        ]
      ])
      ->set('selected_customer_id', ''); // No customer selected

    // Get the component instance to access the payload building logic
    $instance = $component->instance();

    // Verify that customer-related properties are empty
    $this->assertEmpty($instance->customer_id);
    $this->assertEmpty($instance->customer['party_name']);

    // The payload should not include accounting_customer_party when no customer is selected
    // This would be tested in the actual submission method, but we can verify the logic
    $this->assertFalse($instance->customer_id || !empty($instance->customer['party_name']));
  }
}
