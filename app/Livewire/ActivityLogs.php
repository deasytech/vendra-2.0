<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $event = '';
    public string $date_from = '';
    public string $date_to = '';

    public function updated($name): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'event', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->event, fn ($q) => $q->where('event', 'like', $this->event . '%'))
            ->when($this->date_from, fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->latest('id')
            ->paginate(20);

        return view('livewire.activity-logs', [
            'logs' => $logs,
            'categories' => ['auth' => 'Authentication', 'invoice' => 'Invoices', 'customer' => 'Customers',
                'product' => 'Products', 'setting' => 'Settings', 'taxly' => 'Taxly', 'user' => 'Users'],
        ]);
    }
}
