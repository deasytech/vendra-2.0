<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
  protected $model = Tenant::class;

  public function definition(): array
  {
    return [
      'name' => $this->faker->company(),
      'brand' => $this->faker->word(),
      'domain' => $this->faker->domainName(),
      'entity_id' => strtoupper($this->faker->bothify('ENT-####')),
    ];
  }
}
