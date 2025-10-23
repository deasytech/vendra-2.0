<?php

namespace App\Livewire\Auth;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TaxlyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $taxlyService = new TaxlyService();

        try {
            $result = $taxlyService->taxPayerLogin([
                'email' => $validated['email'],
                'password' => $validated['password']
            ]);

            if (!$result || ($result['status'] ?? 500) != "200 OK") {
                throw new \Exception('FIRS authentication failed');
            }

            $validated['entity_id'] = $result['entity_id'] ?? null;
        } catch (\Exception $e) {
            Log::error('TaxPayer login failed', ['email' => $validated['email'], 'error' => $e->getMessage()]);
            $this->addError('email', 'FIRS authentication failed. Please check your credentials.');
            return;
        }

        $tenant = Tenant::create([
            'name'              => $validated['name'],
            'entity_id'         => $validated['entity_id'] ?? null,
        ]);

        if (!empty($validated['entity_id'])) {
            try {
                $getEntity = $taxlyService->getEntity($validated['entity_id']);

                if (!$getEntity || !isset($getEntity['code']) || $getEntity['code'] != 200) {
                    throw new \Exception('Failed to fetch entity details from FIRS');
                }

                // Safely extract organization data with proper null checks
                $organizations = $getEntity['data']['organizations'] ?? $getEntity['data']['businesses'] ?? [];
                if (!empty($organizations) && is_array($organizations)) {
                    $organization = $organizations[0];
                    $validated['tin']         = $organization['tin'] ?? null;
                    $validated['trade_name']  = $organization['name'] ?? null;
                    $validated['business_id'] = $organization['id'] ?? null;
                    $validated['irn_template'] = $organization['irn_template'] ?? null;

                    // Extract service_id from irn_template
                    if (!empty($validated['irn_template'])) {
                        $parts = explode('-', $validated['irn_template']);
                        if (count($parts) >= 2 && !empty($parts[1])) {
                            $validated['service_id'] = $parts[1];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch entity details', ['entity_id' => $validated['entity_id'], 'error' => $e->getMessage()]);
            }
        }

        $org = $tenant->organizations()->create([
            'tin'                   => $validated['tin'] ?? null,
            'trade_name'            => $validated['trade_name'] ?? $validated['name'],
            'business_id'           => $validated['business_id'] ?? null,
            'service_id'            => $validated['service_id'] ?? null,
        ]);

        // Use trade_name if available, otherwise fall back to the user's name
        $userName = $validated['trade_name'] ?? $validated['name'];

        // Create the user with proper relationships
        $user = User::create([
            'tenant_id'     => $tenant->id,
            'organization_id' => $org->id,
            'name'          => $userName,
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
        ]);

        $user->organizations()->attach($org->id);

        event(new Registered($user));

        Auth::login($user);
        Session::regenerate();

        return redirect()->route('dashboard');
    }
}
