<?php

namespace Database\Factories;

use App\Models\InvoiceTaxTotal;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceTaxTotalFactory extends Factory
{
  protected $model = InvoiceTaxTotal::class;

  public function definition(): array
  {
    $taxAmount = $this->faker->randomFloat(2, 50, 500);
    return [
      'invoice_id' => Invoice::factory(),
      'tax_amount' => $taxAmount,
      'tax_subtotal' => [
        [
          'taxable_amount' => $this->faker->randomFloat(2, 500, 5000),
          'tax_amount' => $taxAmount,
          'tax_category' => 'VAT',
          'tax_percent' => 7.5,
        ]
      ],
    ];
  }
}
