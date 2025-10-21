<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'organization_id',
        'business_id',
        'name',
        'tin',
        'service_id',
        'reference',
        'sector',
        'email',
        'telephone',
        'postal_address'
    ];

    protected $casts = ['postal_address' => 'array'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
