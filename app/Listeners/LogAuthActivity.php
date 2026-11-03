<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Events\Dispatcher;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;

class LogAuthActivity
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
            Registered::class => 'onRegistered',
            PasswordReset::class => 'onPasswordReset',
            TwoFactorAuthenticationEnabled::class => 'onTwoFactorEnabled',
            TwoFactorAuthenticationDisabled::class => 'onTwoFactorDisabled',
        ];
    }

    public function onLogin(Login $event): void
    {
        ActivityLogger::log('auth.login', 'User logged in', $event->user, [], $event->user);
    }

    public function onLogout(Logout $event): void
    {
        if ($event->user) {
            ActivityLogger::log('auth.logout', 'User logged out', $event->user, [], $event->user);
        }
    }

    public function onFailed(Failed $event): void
    {
        ActivityLogger::log('auth.failed', 'Failed login attempt', $event->user, [
            'email' => $event->credentials['email'] ?? null,
        ], $event->user);
    }

    public function onRegistered(Registered $event): void
    {
        ActivityLogger::log('auth.registered', 'User registered', $event->user, [], $event->user);
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        ActivityLogger::log('auth.password_reset', 'Password was reset', $event->user, [], $event->user);
    }

    public function onTwoFactorEnabled(TwoFactorAuthenticationEnabled $event): void
    {
        ActivityLogger::log('auth.two_factor_enabled', 'Two-factor authentication enabled', $event->user, [], $event->user);
    }

    public function onTwoFactorDisabled(TwoFactorAuthenticationDisabled $event): void
    {
        ActivityLogger::log('auth.two_factor_disabled', 'Two-factor authentication disabled', $event->user, [], $event->user);
    }
}
