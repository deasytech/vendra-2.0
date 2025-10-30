<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name', 'brand', 'domain', 'entity_id'];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    public function taxlyCredential()
    {
        return $this->hasOne(TaxlyCredential::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
