<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class JobFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $payload;

    /**
     * Create a new notification instance.
     *
     * @param array $payload An array with job_name, exception, failed_at, payload, connection, queue
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;

        // Prefer same queue as main app to ensure delivery; feel free to change.
        $this->onQueue(config('queue.default', 'default'));
    }

    public function via($notifiable): array
    {
        // store to database and send email (if user has email)
        return in_array('mail', $this->viaChannels()) ? ['database', 'mail'] : ['database'];
    }

    protected function viaChannels(): array
    {
        return ['database', 'mail'];
    }

    /**
     * Database representation (used by Filament DB notifications).
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Background job failed: ' . ($this->payload['job_name'] ?? 'Unknown'),
            'job_name' => $this->payload['job_name'] ?? null,
            'exception' => \Illuminate\Support\Str::limit($this->payload['exception'] ?? 'N/A', 800),
            'failed_at' => $this->payload['failed_at'] ?? now()->toDateTimeString(),
            'connection' => $this->payload['connection'] ?? null,
            'queue' => $this->payload['queue'] ?? null,
            'payload' => $this->payload['payload'] ?? null,
        ];
    }

    /**
     * Mail representation.
     */
    public function toMail($notifiable): MailMessage
    {
        $jobName = $this->payload['job_name'] ?? 'Unknown';
        $exception = $this->payload['exception'] ?? 'No message';
        $failedAt = $this->payload['failed_at'] ?? now()->toDateTimeString();
        $queue = $this->payload['queue'] ?? 'default';
        $connection = $this->payload['connection'] ?? 'default';

        $mail = (new MailMessage)
            ->subject("Vendra: Background job failed — {$jobName}")
            ->greeting('Hello')
            ->line("A background job has failed in Vendra.")
            ->line("Job: {$jobName}")
            ->line("Connection: {$connection} — Queue: {$queue}")
            ->line("Failed at: {$failedAt}")
            ->line("Exception: " . \Illuminate\Support\Str::limit($exception, 1000))
            ->line('You can view more details in the Queue Monitor (Filament).')
            ->salutation('Regards, Vendra');

        return $mail;
    }

    // Optionally format broadcast or other channels if you use them.
}
