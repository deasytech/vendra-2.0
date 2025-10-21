<?php

namespace Database\Factories;

use App\Models\InvoiceLine;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineFactory extends Factory
{
  protected $model = InvoiceLine::class;

  public function definition(): array
  {
    $price = $this->faker->randomFloat(2, 100, 1000);
    $qty = $this->faker->numberBetween(1, 10);

    return [
      'invoice_id' => Invoice::factory(),
      'hsn_code' => strtoupper($this->faker->bothify('HSN###')),
      'product_category' => $this->faker->word(),
      'discount_rate' => 0,
      'discount_amount' => 0,
      'fee_rate' => 0,
      'fee_amount' => 0,
      'invoiced_quantity' => $qty,
      'line_extension_amount' => $price * $qty,
      'item' => [
        'name' => $this->faker->word(),
        'description' => $this->faker->sentence(),
      ],
      'price' => [
        'price_amount' => $price,
        'base_quantity' => 1,
        'price_unit' => 'NGN',
      ],
      'order' => 0,
    ];
  }
}
