<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Filament::auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        // Example multi-tenant scoping
        $tenantId = $user->tenant_id ?? $user->organization?->tenant_id;
        $usersQuery = $isSuperAdmin
            ? User::query()
            : User::where('organization_id', $user->organization_id);

        $customersQuery = $isSuperAdmin
            ? Customer::query()
            : Customer::when($tenantId, fn($query) => $query->where('tenant_id', $tenantId));

        // Growth calculation (last 30 days)
        $lastMonth = Carbon::now()->subDays(30);

        $newUsers = (clone $usersQuery)->where('created_at', '>=', $lastMonth)->count();
        $newCustomers = (clone $customersQuery)->where('created_at', '>=', $lastMonth)->count();

        return [

            Stat::make('Users', $usersQuery->count())
                ->description("$newUsers added in 30 days")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-users')
                ->url(route('filament.admin.resources.users.index')),

            Stat::make('Customers', $customersQuery->count())
                ->description("$newCustomers added in 30 days")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->icon('heroicon-o-user-group')
                ->url(route('filament.admin.resources.customers.index')),

            Stat::make('Organizations', $isSuperAdmin ? Organization::count() : 1)
                ->description($isSuperAdmin ? 'System wide' : 'Your organization')
                ->color('info')
                ->icon('heroicon-o-building-office'),

            Stat::make('Tenants', $isSuperAdmin ? Tenant::count() : ($tenantId ? Tenant::where('id', $tenantId)->count() : 0))
                ->description('Active tenants')
                ->color('warning')
                ->icon('heroicon-o-home'),
        ];
    }
}
