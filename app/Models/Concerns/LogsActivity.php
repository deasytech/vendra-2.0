<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogger;

/**
 * Records created / updated / deleted activity for a model.
 * Override $activityLabel / $activityIgnore on the model to tune output.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => $model->recordActivity('created'));

        static::updated(function ($model) {
            $changes = ActivityLogger::redact(
                collect($model->getChanges())->except(array_merge(['updated_at'], $model->activityIgnore ?? []))->all()
            );

            if (empty($changes)) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $changes);

            $model->recordActivity('updated', [
                'old' => ActivityLogger::redact($old),
                'new' => $changes,
            ]);
        });

        static::deleted(fn ($model) => $model->recordActivity('deleted'));

        if (method_exists(static::class, 'restored')) {
            static::restored(fn ($model) => $model->recordActivity('restored'));
        }
    }

    public function recordActivity(string $action, array $properties = []): void
    {
        $label = $this->activityLabel ?? class_basename($this);
        $name = $this->activityName();

        ActivityLogger::log(
            strtolower($label) . '.' . $action,
            ucfirst($label) . ($name ? " {$name}" : '') . " {$action}",
            $this,
            $properties,
        );
    }

    protected function activityName(): ?string
    {
        foreach (['invoice_reference', 'name', 'key'] as $attr) {
            if (! empty($this->{$attr})) {
                return (string) $this->{$attr};
            }
        }

        return $this->getKey() ? "#{$this->getKey()}" : null;
    }
}
