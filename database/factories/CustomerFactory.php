<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
  protected $model = Customer::class;

  public function definition(): array
  {
    return [
      'name' => $this->faker->company(),
      'tin' => strtoupper($this->faker->bothify('TIN#######')),
      'email' => $this->faker->companyEmail(),
      'phone' => $this->faker->phoneNumber(),
      'business_description' => $this->faker->catchPhrase(),
      'street_name' => $this->faker->streetAddress(),
      'city_name' => $this->faker->city(),
      'postal_zone' => $this->faker->postcode(),
      'state' => $this->faker->state(),
      'country' => 'Nigeria',
    ];
  }
}
