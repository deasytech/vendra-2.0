<?php

use App\Livewire\ActivityLogs;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Livewire\Livewire;

function tenantUser(): User
{
    $tenant = Tenant::create(['name' => 'Acme']);

    return User::factory()->create(['tenant_id' => $tenant->id]);
}

it('logs model create, update and delete with the acting user', function () {
    $user = tenantUser();
    $this->actingAs($user);

    $customer = Customer::create(['tenant_id' => $user->tenant_id, 'name' => 'Foo Ltd', 'tin' => '12345']);
    $customer->update(['name' => 'Bar Ltd']);
    $customer->delete();

    $events = ActivityLog::pluck('event')->all();
    expect($events)->toContain('customer.created', 'customer.updated', 'customer.deleted');

    $updated = ActivityLog::where('event', 'customer.updated')->first();
    expect($updated->user_id)->toBe($user->id)
        ->and($updated->tenant_id)->toBe($user->tenant_id)
        ->and($updated->properties['old']['name'])->toBe('Foo Ltd')
        ->and($updated->properties['new']['name'])->toBe('Bar Ltd');
});

it('redacts sensitive attributes', function () {
    $user = tenantUser();
    $this->actingAs($user);

    $user->update(['password' => 'new-secret-password']);

    $log = ActivityLog::where('event', 'user.updated')->first();
    expect(json_encode($log->properties))->not->toContain('new-secret-password')
        ->and($log->properties['new']['password'])->toBe('[redacted]');
});

it('logs authentication events', function () {
    $user = tenantUser();

    event(new Login('web', $user, false));

    expect(ActivityLog::where('event', 'auth.login')->where('user_id', $user->id)->exists())->toBeTrue();
});

it('does not log updates with no meaningful changes', function () {
    $user = tenantUser();
    $this->actingAs($user);
    $customer = Customer::create(['tenant_id' => $user->tenant_id, 'name' => 'Foo', 'tin' => '12345']);
    ActivityLog::query()->delete();

    $customer->touch();

    expect(ActivityLog::count())->toBe(0);
});

it('only shows a tenant its own logs', function () {
    $user = tenantUser();
    $other = tenantUser();

    ActivityLogger::log('test.mine', 'Mine', null, [], $user);
    ActivityLogger::log('test.theirs', 'Theirs', null, [], $other);

    Livewire::actingAs($user)->test(ActivityLogs::class)
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});
