<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Invoices\InvoicesIndex;

class InvoiceCancellationTest extends TestCase
{
  use RefreshDatabase;

  public function test_invoice_cancellation_modal_renders_correctly()
  {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
      'customer_id' => $customer->id,
      'invoice_reference' => 'TEST-001',
      'irn' => null, // Not transmitted
      'payment_status' => 'pending',
    ]);

    $component = Livewire::actingAs($user)->test(InvoicesIndex::class);

    // Check that the modal trigger exists
    $component->assertSee('confirm-invoice-cancellation-' . $invoice->id);

    // Check that the modal content is rendered
    $component->assertSee('Cancel Invoice');
    $component->assertSee('Are you sure you want to cancel invoice');
    $component->assertSee('TEST-001');
  }

  public function test_invoice_cancellation_works_for_non_transmitted_invoices()
  {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
      'customer_id' => $customer->id,
      'invoice_reference' => 'TEST-001',
      'irn' => null, // Not transmitted
      'payment_status' => 'pending',
    ]);

    $component = Livewire::actingAs($user)->test(InvoicesIndex::class);

    // Call the deleteInvoice method
    $component->call('deleteInvoice', $invoice->id);

    // Assert success message
    $component->assertDispatched('success', message: 'Invoice cancelled successfully.');

    // Assert modal close event
    $component->assertDispatched('close-modal', 'confirm-invoice-cancellation-' . $invoice->id);

    // Assert invoice is soft deleted
    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
  }

  public function test_invoice_cancellation_fails_for_transmitted_invoices()
  {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
      'customer_id' => $customer->id,
      'invoice_reference' => 'TEST-001',
      'irn' => 'TAXLY-123456', // Transmitted
      'payment_status' => 'pending',
    ]);

    $component = Livewire::actingAs($user)->test(InvoicesIndex::class);

    // Call the deleteInvoice method
    $component->call('deleteInvoice', $invoice->id);

    // Assert error message
    $component->assertDispatched('error', message: 'Cannot delete an invoice that has been transmitted to Taxly.');

    // Assert invoice is NOT deleted
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
  }

  public function test_cancellation_modal_shows_warning_for_transmitted_invoices()
  {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
      'customer_id' => $customer->id,
      'invoice_reference' => 'TEST-001',
      'irn' => 'TAXLY-123456', // Transmitted
      'payment_status' => 'pending',
    ]);

    $component = Livewire::actingAs($user)->test(InvoicesIndex::class);

    // Check that the warning message is shown for transmitted invoices
    $component->assertSee('This invoice has been transmitted to Taxly');
    $component->assertSee('TAXLY-123456');
    $component->assertSee('Cancelling it here will not affect the transmission status');
  }

  public function test_cancellation_modal_has_proper_styling()
  {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $invoice = Invoice::factory()->create([
      'customer_id' => $customer->id,
      'invoice_reference' => 'TEST-001',
      'irn' => null,
      'payment_status' => 'pending',
    ]);

    $component = Livewire::actingAs($user)->test(InvoicesIndex::class);

    // Check for proper modal structure and styling
    $component->assertSee('max-w-lg'); // Modal size
    $component->assertSee('bg-red-100'); // Warning icon background
    $component->assertSee('text-red-600'); // Warning icon color
    $component->assertSee('Yes, cancel invoice'); // Confirm button
    $component->assertSee('No, keep invoice'); // Cancel button
  }
}
