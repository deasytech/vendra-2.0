<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceTransmission;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxlyWebhookTest extends TestCase
{
  use RefreshDatabase;

  public function test_webhook_handles_transmission_response()
  {
    // Create a minimal tenant first
    $tenant = Tenant::factory()->create();

    // Create a test invoice with IRN manually to avoid factory issues
    $invoice = Invoice::create([
      'tenant_id' => $tenant->id,
      'invoice_reference' => 'TEST-INV-001',
      'irn' => 'TEST-IRN-123456',
      'transmit' => 'PENDING',
      'payment_status' => 'PENDING'
    ]);

    // Simulate webhook payload from transmitByIrn
    $webhookPayload = [
      'irn' => 'TEST-IRN-123456',
      'status' => 'success',
      'transmission_id' => 'TRANS-789',
      'message' => 'Invoice transmitted successfully',
      'error' => null,
      'transmitted_at' => now()->toISOString()
    ];

    // Send webhook request
    $response = $this->postJson('/api/taxly/webhook/invoice', $webhookPayload);

    // Assert response
    $response->assertStatus(200)
      ->assertJson([
        'status' => 'success',
        'message' => 'Webhook processed'
      ]);

    // Assert invoice was updated
    $this->assertEquals('TRANSMITTED', $invoice->fresh()->transmit);

    // Assert transmission record was created
    $this->assertDatabaseHas('invoice_transmissions', [
      'invoice_id' => $invoice->id,
      'irn' => 'TEST-IRN-123456',
      'status' => 'success',
      'message' => 'Invoice transmitted successfully'
    ]);
  }

  public function test_webhook_handles_failed_transmission()
  {
    // Create a minimal tenant first
    $tenant = Tenant::factory()->create();

    // Create a test invoice with IRN manually to avoid factory issues
    $invoice = Invoice::create([
      'tenant_id' => $tenant->id,
      'invoice_reference' => 'TEST-INV-002',
      'irn' => 'TEST-IRN-789012',
      'transmit' => 'PENDING',
      'payment_status' => 'PENDING'
    ]);

    // Simulate failed webhook payload
    $webhookPayload = [
      'irn' => 'TEST-IRN-789012',
      'status' => 'failed',
      'message' => 'Transmission failed',
      'error' => 'Invalid invoice data',
      'transmitted_at' => null
    ];

    // Send webhook request
    $response = $this->postJson('/api/taxly/webhook/invoice', $webhookPayload);

    // Assert response
    $response->assertStatus(200);

    // Assert invoice was not marked as transmitted
    $this->assertEquals('FAILED', $invoice->fresh()->transmit);

    // Assert transmission record was created with error
    $this->assertDatabaseHas('invoice_transmissions', [
      'invoice_id' => $invoice->id,
      'irn' => 'TEST-IRN-789012',
      'status' => 'failed',
      'error' => 'Invalid invoice data'
    ]);
  }

  public function test_webhook_handles_missing_irn()
  {
    // Send webhook without IRN
    $response = $this->postJson('/api/taxly/webhook/invoice', [
      'status' => 'success',
      'message' => 'No IRN provided'
    ]);

    // Should still return success but log warning
    $response->assertStatus(200)
      ->assertJson([
        'status' => 'success',
        'message' => 'Webhook processed'
      ]);
  }

  public function test_webhook_handles_nonexistent_invoice()
  {
    $webhookPayload = [
      'irn' => 'NONEXISTENT-IRN',
      'status' => 'success',
      'message' => 'Invoice not found'
    ];

    $response = $this->postJson('/api/taxly/webhook/invoice', $webhookPayload);

    $response->assertStatus(200);

    // Should not create any transmission records
    $this->assertDatabaseCount('invoice_transmissions', 0);
  }
}
