<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\JobFailedNotification;
use Throwable;

class NotifySuperAdminsOnJobFailed
{
    /**
     * Handle the event.
     */
    public function handle(JobFailed $event): void
    {
        try {
            // Try to extract meaningful job information
            $payload = $this->getPayload($event);
            $jobName = $this->getJobName($event, $payload);
            $connection = $event->connectionName ?? 'default';
            $queue = $event->job?->getQueue() ?? ($payload['queue'] ?? 'default');

            $data = [
                'job_name'       => $jobName,
                'connection'     => $connection,
                'queue'          => $queue,
                'exception'      => $this->getExceptionMessage($event),
                'failed_at'      => now()->toDateTimeString(),
                'payload'        => $payload,
            ];

            // Log full failure for debugging/alerting systems
            Log::error('Queue job failed', $data + [
                'trace' => $this->getExceptionTrace($event),
            ]);

            // Notify all super admin users (Spatie role)
            $superAdmins = app(config('auth.providers.users.model'))->whereHas('roles', function ($q) {
                $q->where('name', 'super admin');
            })->get();

            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new JobFailedNotification($data));
            } else {
                // Fallback: log that no super admins were found
                Log::warning('JobFailed: no users with role "super admin" found to notify.');
            }
        } catch (Throwable $e) {
            // Catch everything to ensure the Listener never throws (best-effort only)
            Log::critical('NotifySuperAdminsOnJobFailed listener failed', [
                'listener_exception' => $e->getMessage(),
                'listener_trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function getPayload(JobFailed $event): array
    {
        try {
            $payload = $event->job->payload();
            return is_array($payload) ? $payload : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    protected function getJobName(JobFailed $event, array $payload): string
    {
        // commandName (serialized command) or displayName is common
        if (!empty($payload['data']['commandName'])) {
            return $payload['data']['commandName'];
        }

        if (!empty($payload['displayName'])) {
            return $payload['displayName'];
        }

        // If serialized command exists, try to get short class name
        if (!empty($payload['data']['command'])) {
            // command may be serialized; attempt to unserialize safely
            try {
                $command = @unserialize($payload['data']['command']);
                if (is_object($command)) {
                    return get_class($command);
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        // last-resort name
        return 'UnknownJob';
    }

    protected function getExceptionMessage(JobFailed $event): string
    {
        return $event->exception?->getMessage() ?? 'No exception message';
    }

    protected function getExceptionTrace(JobFailed $event): string
    {
        return $event->exception?->getTraceAsString() ?? '';
    }
}
