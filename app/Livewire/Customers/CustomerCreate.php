<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CustomerCreate extends Component
{
  public $name = '';
  public $tin = '';
  public $email = '';
  public $phone = '';
  public $business_description = '';
  public $street_name = '';
  public $city_name = '';
  public $postal_zone = '';
  public $state = '';
  public $country = 'NG';

  protected $rules = [
    'name' => 'required|string|max:255',
    'tin' => 'nullable|string|max:20',
    'email' => 'nullable|email|max:255',
    'phone' => 'nullable|string|max:20',
    'business_description' => 'nullable|string',
    'street_name' => 'nullable|string|max:255',
    'city_name' => 'nullable|string|max:255',
    'postal_zone' => 'nullable|string|max:20',
    'state' => 'nullable|string|max:255',
    'country' => 'nullable|string|max:2',
  ];

  public function save()
  {
    $this->validate();

    Customer::create([
      'tenant_id' => Auth::user()->tenant_id ?? null,
      'name' => $this->name,
      'tin' => $this->tin,
      'email' => $this->email,
      'phone' => $this->phone,
      'business_description' => $this->business_description,
      'street_name' => $this->street_name,
      'city_name' => $this->city_name,
      'postal_zone' => $this->postal_zone,
      'state' => $this->state,
      'country' => $this->country,
    ]);

    session()->flash('message', 'Customer created successfully.');

    return redirect()->route('customers.index');
  }

  public function render()
  {
    return view('livewire.customers.customer-create');
  }
}
