<?php

namespace App\Livewire\Auth;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public ?Organization $organization = null;

    /**
     * Bind the tenant-branded organization (resolved via the /login/{organization:slug}
     * route) into the container so the auth layout's logo/branding components can
     * resolve the correct tenant scope before any user is authenticated.
     *
     * Note: this is intentionally read from the route rather than declared as a
     * mount() parameter — Livewire's implicit model binding treats any Eloquent-typed
     * mount() parameter as required, and attempts (and fails) to bind it even on the
     * plain /login route where no {organization} segment exists.
     */
    public function mount(): void
    {
        $organization = request()->route('organization');

        if ($organization instanceof Organization) {
            $this->organization = $organization;
            app()->instance('currentOrganization', $organization);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login()
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $user = $this->validateCredentials();

        if (Features::canManageTwoFactorAuthentication() && $user->hasEnabledTwoFactorAuthentication()) {
            Session::put([
                'login.id' => $user->getKey(),
                'login.remember' => $this->remember,
            ]);

            $this->redirect(route('two-factor.login'), navigate: true);

            return;
        }

        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // Prefer an explicit intended redirect from the session to ensure Livewire navigates correctly.
        $intended = Session::pull('url.intended');

        if ($intended) {
            // Use standard redirect for intended URLs
            return redirect()->to($intended);
        }

        // Use Laravel's redirect helper for a full page redirect to dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Validate the user's credentials.
     */
    protected function validateCredentials(): User
    {
        $user = Auth::getProvider()->retrieveByCredentials(['email' => $this->email, 'password' => $this->password]);

        if (! $user || ! Auth::getProvider()->validateCredentials($user, ['password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return $user;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}
