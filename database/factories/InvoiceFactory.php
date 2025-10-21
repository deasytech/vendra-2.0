<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Organization;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
  protected $model = Invoice::class;

  public function definition(): array
  {
    return [
      'tenant_id' => Tenant::factory(),
      'organization_id' => Organization::factory(),
      'business_id' => Business::factory(),
      'invoice_reference' => strtoupper(Str::random(8)),
      'issue_date' => $this->faker->date(),
      'due_date' => $this->faker->date(),
      'payment_status' => 'PENDING',
      'accounting_supplier_party' => [
        'party_name' => $this->faker->company(),
        'email' => $this->faker->companyEmail(),
      ],
      'accounting_customer_party' => [
        'party_name' => $this->faker->name(),
        'email' => $this->faker->email(),
      ],
      'legal_monetary_total' => [
        'payable_amount' => $this->faker->randomFloat(2, 1000, 50000)
      ],
    ];
  }
}
