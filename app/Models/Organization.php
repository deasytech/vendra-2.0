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
        'tin',
        'business_id',
        'registration_number',
        'legal_name',
        'slug',
        'email',
        'phone',
        'postal_address',
        'description'
    ];

    protected $casts = ['postal_address' => 'array'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Convert this organization into a FIRS-compliant Party object.
     */
    public function toPartyObject(): array
    {
        $address = $this->postal_address ?? [];

        return [
            "party_name"           => $this->legal_name,
            "tin"                  => $this->tin,
            "email"                => $this->email,
            "telephone"            => $this->normalizeTelephone($this->phone),
            "business_description" => $this->description,
            "postal_address" => [
                "street_name" => $address['street_name'] ?? 'Unknown Street',
                "city_name"   => $address['city_name'] ?? 'Unknown City',
                "postal_zone" => $address['postal_zone'] ?? '000000',
                "country"     => $address['country'] ?? 'NG',
            ],
        ];
    }

    /**
     * Normalize phone numbers to international format (+234...).
     */
    protected function normalizeTelephone(?string $telephone): ?string
    {
        if (!$telephone) {
            return null;
        }

        $telephone = preg_replace('/\D+/', '', $telephone);

        if (str_starts_with($telephone, '0')) {
            $telephone = '+234' . substr($telephone, 1);
        }

        if (str_starts_with($telephone, '234') && !str_starts_with($telephone, '+')) {
            $telephone = '+' . $telephone;
        }

        return $telephone;
    }
}
