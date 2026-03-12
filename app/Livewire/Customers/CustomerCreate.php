<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerCreate extends Component
{
  use WithFileUploads;

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

  // Logo upload
  public $logo;

  protected $rules = [
    'name' => 'required|string|max:255',
    'tin' => 'nullable|string|max:20',
    'email' => 'required|email|max:255',
    'phone' => 'required|string|max:20',
    'business_description' => 'nullable|string',
    'street_name' => 'required|string|max:255',
    'city_name' => 'required|string|max:255',
    'postal_zone' => 'required|string|max:20',
    'state' => 'required|string|max:255',
    'country' => 'required|string|max:2',
    'logo' => 'nullable|image|max:2048', // Max 2MB
  ];

  public function save()
  {
    $this->validate();

    try {
      $customer = Customer::create([
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
        'logo_path' => null,
      ]);

      // Handle logo upload if provided
      if ($this->logo) {
        $logoPath = $this->logo->store("customers/{$customer->id}/logos", 'public');
        $customer->update(['logo_path' => $logoPath]);
      }

      session()->flash('message', 'Customer created successfully.');

      return redirect()->route('customers.index');
    } catch (\Exception $e) {
      session()->flash('error', 'Error creating customer: ' . $e->getMessage());
    }
  }

  public function render()
  {
    return view('livewire.customers.customer-create');
  }
}
