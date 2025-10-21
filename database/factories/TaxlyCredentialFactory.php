<?php

namespace Database\Factories;

use App\Models\TaxlyCredential;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxlyCredentialFactory extends Factory
{
  protected $model = TaxlyCredential::class;

  public function definition(): array
  {
    return [
      'tenant_id' => Tenant::factory(),
      'auth_type' => 'api_key',
      'api_key' => 'demo-key-' . $this->faker->sha256(),
      'base_url' => 'https://taxly.ng',
    ];
  }
}
