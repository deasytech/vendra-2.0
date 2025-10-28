<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
  public $showOrganizationModal = false;
  public $organization;

  // Form fields for organization completion
  public $legal_name;
  public $email;
  public $phone;
  public $registration_number;
  public $postal_address = [
    'street_name' => '',
    'city_name' => '',
    'postal_zone' => '',
    'country' => ''
  ];
  public $description;

  public function mount()
  {
    $this->organization = Auth::user()->organization;

    // Initialize form fields with existing data
    if ($this->organization) {
      $this->legal_name = $this->organization->legal_name ?? '';
      $this->email = $this->organization->email ?? '';
      $this->phone = $this->organization->phone ?? '';
      $this->registration_number = $this->organization->registration_number ?? '';
      $this->postal_address = is_array($this->organization->postal_address)
        ? $this->organization->postal_address
        : (is_string($this->organization->postal_address)
          ? json_decode($this->organization->postal_address, true)
          : [
            'street_name' => '',
            'city_name' => '',
            'postal_zone' => '',
            'country' => ''
          ]);
      $this->description = $this->organization->description ?? '';

      // Check if we should show the modal immediately
      $this->checkAndShowModal();
    }
  }

  protected function rules(): array
  {
    return [
      'legal_name' => 'required|string|max:255',
      'email' => 'required|email|max:255',
      'phone' => 'required|string|max:20',
      'registration_number' => 'nullable|string|max:100',
      'postal_address.street_name' => 'required|string|max:255',
      'postal_address.city_name' => 'required|string|max:255',
      'postal_address.postal_zone' => 'nullable|string|max:100',
      'postal_address.country' => 'required|string|size:2',
      'description' => 'nullable|string|max:1000',
    ];
  }

  public function checkAndShowModal()
  {
    if ($this->shouldShowModal()) {
      $this->showOrganizationModal = true;
    }
  }

  public function shouldShowModal()
  {
    if (!$this->organization) {
      return false;
    }

    // Check if required fields are missing (excluding description)
    $requiredFields = ['legal_name', 'email', 'phone'];

    foreach ($requiredFields as $field) {
      $value = $this->organization->$field;
      if (empty($value) || $value === '') {
        return true;
      }
    }

    return false;
  }

  public function saveOrganizationDetails()
  {
    try {
      // Validate the form data
      $this->validate();

      if (!$this->organization) {
        session()->flash('error', 'Organization not found.');
        return;
      }

      // Update the organization with the form data
      $this->organization->update([
        'legal_name' => $this->legal_name,
        'email' => $this->email,
        'phone' => $this->phone,
        'registration_number' => $this->registration_number,
        'postal_address' => $this->postal_address,
        'description' => $this->description,
      ]);

      // Close modal and show success message
      $this->showOrganizationModal = false;
      session()->flash('success', 'Organization details updated successfully!');

      // Refresh organization data
      $this->organization->refresh();
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Let Livewire handle validation errors automatically
      throw $e;
    } catch (\Exception $e) {
      session()->flash('error', 'Failed to update organization: ' . $e->getMessage());
    }
  }

  public function closeModal()
  {
    $this->showOrganizationModal = false;
  }

  public function render()
  {
    return view('livewire.dashboard');
  }
}
