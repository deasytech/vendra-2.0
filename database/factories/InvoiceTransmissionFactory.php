<?php

namespace Database\Factories;

use App\Models\InvoiceTransmission;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceTransmissionFactory extends Factory
{
  protected $model = InvoiceTransmission::class;

  public function definition(): array
  {
    return [
      'invoice_id' => Invoice::factory(),
      'action' => $this->faker->randomElement(['submit', 'transmit', 'confirm']),
      'request_payload' => ['ref' => Str::uuid()],
      'response_payload' => ['status' => 'success'],
      'status' => 'success',
    ];
  }
}
