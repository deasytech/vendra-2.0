<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxlyCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'tenant_name',
        'auth_type',
        'api_key',
        'api_key_id',
        'api_key_permissions',
        'token',
        'token_expires_at',
        'base_url',
        'meta',
        'is_integrator',
        'integrator_status',
        'integrator_contact_email',
    ];

    protected $casts = [
        'meta' => 'array',
        'api_key_permissions' => 'array',
        'token_expires_at' => 'datetime',
        'is_integrator' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
