<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerEdit extends Component
{
  use WithFileUploads;

  public Customer $customer;
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
  public $existing_logo;

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
    'logo' => 'nullable|image|max:2048', // Max 2MB
  ];

  public function mount(Customer $customer)
  {
    $this->customer = $customer;
    $this->name = $customer->name;
    $this->tin = $customer->tin;
    $this->email = $customer->email;
    $this->phone = $customer->phone;
    $this->business_description = $customer->business_description;
    $this->street_name = $customer->street_name;
    $this->city_name = $customer->city_name;
    $this->postal_zone = $customer->postal_zone;
    $this->state = $customer->state;
    $this->country = $customer->country;
    $this->existing_logo = $customer->logo_path;
  }

  public function update()
  {
    $this->validate();

    try {
      // Handle logo upload
      if ($this->logo) {
        // Delete old logo if exists
        if ($this->existing_logo) {
          Storage::disk('public')->delete($this->existing_logo);
        }

        // Store new logo with customer-specific path
        $logoPath = $this->logo->store("customers/{$this->customer->id}/logos", 'public');
        $this->customer->logo_path = $logoPath;
      }

      $this->customer->update([
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

      session()->flash('message', 'Customer updated successfully.');

      return redirect()->route('customers.index');
    } catch (\Exception $e) {
      session()->flash('error', 'Error updating customer: ' . $e->getMessage());
    }
  }

  public function removeLogo()
  {
    if ($this->existing_logo) {
      Storage::disk('public')->delete($this->existing_logo);
      $this->customer->logo_path = null;
      $this->customer->save();
      $this->existing_logo = null;
      session()->flash('message', 'Logo removed successfully.');
    }
  }

  public function render()
  {
    return view('livewire.customers.customer-edit');
  }
}
