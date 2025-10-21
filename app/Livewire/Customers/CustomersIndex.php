<?php

namespace App\Livewire\Customers;

use App\Models\Business;
use Livewire\Component;

class CustomersIndex extends Component
{
    public function render()
    {
        $businesses = Business::latest()->paginate(10);

        return view('livewire.customers.customers-index', [
            'customers' => $businesses,
        ]);
    }
}
