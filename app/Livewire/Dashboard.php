<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
  public $showOrganizationModal = false;
  public $organization;

  // Form fields for organization completion
  public $email;
  public $phone;
  public $registration_number;
  public $postal_address = [
    'street_name' => '',
    'city_name' => '',
    'state_name' => '',
    'postal_zone' => '',
    'country' => ''
  ];
  public $description;

  // Statistics data
  public $totalInvoices = 0;
  public $totalInvoicesChange = 0;
  public $totalInvoicesTrend = 'neutral';
  public $pendingInvoices = 0;
  public $totalCustomers = 0;
  public $totalCustomersChange = 0;
  public $totalCustomersTrend = 'neutral';
  public $revenueThisMonth = 0;
  public $revenueChange = 0;
  public $revenueTrend = 'neutral';

  public function mount()
  {
    $this->organization = Auth::user()->organization;

    // Initialize form fields with existing data
    if ($this->organization) {
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
            'state_name' => '',
            'postal_zone' => '',
            'country' => ''
          ]);
      $this->description = $this->organization->description ?? '';

      // Calculate dashboard statistics
      $this->calculateStatistics();

      // Temporarily disable automatic modal showing to allow dashboard access
      $this->checkAndShowModal();

      // Log debug information
      logger()->info('Dashboard mounted', [
        'organization_id' => $this->organization->id,
        'should_show_modal' => $this->shouldShowModal(),
        'email' => $this->email,
        'phone' => $this->phone,
        'postal_address' => $this->postal_address
      ]);
    }
  }

  protected function calculateStatistics()
  {
    $tenantId = Auth::user()->tenant_id;
    $now = now();

    // Calculate Total Invoices and change
    $this->totalInvoices = Invoice::where('tenant_id', $tenantId)->count();
    $lastMonthTotal = Invoice::where('tenant_id', $tenantId)
      ->whereMonth('created_at', $now->copy()->subMonth()->month)
      ->whereYear('created_at', $now->copy()->subMonth()->year)
      ->count();

    if ($lastMonthTotal > 0) {
      $this->totalInvoicesChange = round((($this->totalInvoices - $lastMonthTotal) / $lastMonthTotal) * 100);
      $this->totalInvoicesTrend = $this->totalInvoicesChange >= 0 ? 'up' : 'down';
    } else {
      $this->totalInvoicesChange = 0;
      $this->totalInvoicesTrend = $this->totalInvoices > 0 ? 'new' : 'neutral';
    }

    // Calculate Pending Invoices
    $this->pendingInvoices = Invoice::where('tenant_id', $tenantId)
      ->where('payment_status', 'PENDING')
      ->count();

    // Calculate Total Customers and change
    $this->totalCustomers = Customer::where('tenant_id', $tenantId)->count();
    $lastMonthCustomers = Customer::where('tenant_id', $tenantId)
      ->whereMonth('created_at', $now->copy()->subMonth()->month)
      ->whereYear('created_at', $now->copy()->subMonth()->year)
      ->count();

    if ($lastMonthCustomers > 0) {
      $this->totalCustomersChange = round((($this->totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100);
      $this->totalCustomersTrend = $this->totalCustomersChange >= 0 ? 'up' : 'down';
    } else {
      $this->totalCustomersChange = 0;
      $this->totalCustomersTrend = $this->totalCustomers > 0 ? 'new' : 'neutral';
    }

    // Calculate Revenue This Month and change
    $this->revenueThisMonth = Invoice::where('tenant_id', $tenantId)
      ->whereMonth('created_at', $now->month)
      ->whereYear('created_at', $now->year)
      ->sum('legal_monetary_total->payable_amount') ?? 0;

    $lastMonthRevenue = Invoice::where('tenant_id', $tenantId)
      ->whereMonth('created_at', $now->copy()->subMonth()->month)
      ->whereYear('created_at', $now->copy()->subMonth()->year)
      ->sum('legal_monetary_total->payable_amount') ?? 0;

    if ($lastMonthRevenue > 0) {
      $this->revenueChange = round((($this->revenueThisMonth - $lastMonthRevenue) / $lastMonthRevenue) * 100);
      $this->revenueTrend = $this->revenueChange >= 0 ? 'up' : 'down';
    } else {
      $this->revenueChange = 0;
      $this->revenueTrend = $this->revenueThisMonth > 0 ? 'new' : 'neutral';
    }

    // Log statistics for debugging
    logger()->info('Dashboard statistics calculated', [
      'totalInvoices' => $this->totalInvoices,
      'totalInvoicesChange' => $this->totalInvoicesChange,
      'totalInvoicesTrend' => $this->totalInvoicesTrend,
      'pendingInvoices' => $this->pendingInvoices,
      'totalCustomers' => $this->totalCustomers,
      'totalCustomersChange' => $this->totalCustomersChange,
      'totalCustomersTrend' => $this->totalCustomersTrend,
      'revenueThisMonth' => $this->revenueThisMonth,
      'revenueChange' => $this->revenueChange,
      'revenueTrend' => $this->revenueTrend,
    ]);
  }

  protected function rules(): array
  {
    return [
      'phone' => 'required|string|max:20',
      'registration_number' => 'nullable|string|max:100',
      'postal_address.street_name' => 'required|string|max:255',
      'postal_address.city_name' => 'required|string|max:255',
      'postal_address.state_name' => 'required|string|max:255',
      'postal_address.postal_zone' => 'nullable|string|max:100',
      'postal_address.country' => 'required|string|size:2',
      'description' => 'nullable|string|min:50|max:1000',
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
    $requiredFields = ['postal_address', 'email', 'phone'];

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
