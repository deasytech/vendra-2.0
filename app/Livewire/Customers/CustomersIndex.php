<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;

class CustomersIndex extends Component
{
    public function deleteCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);

        // Check if customer has any invoices
        if ($customer->invoices()->exists()) {
            session()->flash('error', 'Cannot delete customer with existing invoices.');
            return;
        }

        $customer->delete();
        session()->flash('message', 'Customer deleted successfully.');
    }

    public function render()
    {
        $customers = Customer::latest()->paginate(10);

        return view('livewire.customers.customers-index', [
            'customers' => $customers,
        ]);
    }
}
