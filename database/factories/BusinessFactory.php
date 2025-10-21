<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
  protected $model = Business::class;

  public function definition(): array
  {
    return [
      'organization_id' => Organization::factory(),
      'business_id' => $this->faker->uuid(),
      'name' => $this->faker->company(),
      'tin' => strtoupper($this->faker->bothify('TIN#######')),
      'email' => $this->faker->companyEmail(),
      'telephone' => $this->faker->phoneNumber(),
      'postal_address' => json_encode([
        'street' => $this->faker->streetAddress(),
        'city' => $this->faker->city(),
        'country' => 'Nigeria'
      ]),
    ];
  }
}
