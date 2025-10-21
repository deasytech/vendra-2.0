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
        'auth_type',
        'api_key',
        'token',
        'token_expires_at',
        'base_url',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'token_expires_at' => 'datetime',
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
