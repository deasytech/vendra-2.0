<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
  protected $model = Organization::class;

  public function definition(): array
  {
    return [
      'tenant_id' => Tenant::factory(),
      'service_id' => $this->faker->uuid(),
      'registration_number' => strtoupper($this->faker->bothify('RC#######')),
      'legal_name' => $this->faker->company(),
      'email' => $this->faker->companyEmail(),
      'phone' => $this->faker->phoneNumber(),
      'city_name' => $this->faker->city(),
      'postal_zone' => $this->faker->postcode(),
      'description' => $this->faker->sentence(),
    ];
  }
}
