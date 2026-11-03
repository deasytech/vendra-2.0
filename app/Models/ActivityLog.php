<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withoutGlobalScopes();
    }

    /** Who performed the action: the user, or the non-user source (e.g. Taxly webhook). */
    public function getActorLabelAttribute(): string
    {
        return $this->user?->name ?? ('System (' . ($this->properties['source'] ?? 'System') . ')');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScopes()->withTrashed();
    }
}
