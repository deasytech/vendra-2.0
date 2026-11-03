<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogger
{
    /** Attributes that must never be written to the log. */
    public const REDACTED = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'api_key', 'token', 'secret', 'value',
    ];

    /**
     * Record an activity. Never throws: logging must not break the user's action.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function log(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?\Illuminate\Contracts\Auth\Authenticatable $user = null,
    ): ?ActivityLog {
        try {
            $user ??= Auth::user();
            $request = app()->bound('request') ? request() : null;
            $inConsole = app()->runningInConsole() && ! app()->runningUnitTests();

            return ActivityLog::withoutGlobalScopes()->create([
                'tenant_id' => $user->tenant_id ?? $subject?->tenant_id ?? null,
                'user_id' => $user?->getAuthIdentifier(),
                'event' => $event,
                'description' => $description,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'properties' => $properties ?: null,
                'ip_address' => $inConsole ? null : $request?->ip(),
                'user_agent' => $inConsole ? null : (substr((string) $request?->userAgent(), 0, 500) ?: null),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to record activity log', ['event' => $event, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Remove sensitive keys from an attribute array.
     */
    public static function redact(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, self::REDACTED, true)) {
                $attributes[$key] = '[redacted]';
            }
        }

        return $attributes;
    }
}
