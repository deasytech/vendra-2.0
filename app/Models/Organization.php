<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'tenant_id',
        'service_id',
        'registration_number',
        'legal_name',
        'email',
        'phone',
        'street_name',
        'city_name',
        'postal_zone',
        'description'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
