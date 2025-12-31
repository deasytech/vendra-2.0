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

        // Handle phone numbers starting with '+'
        if (str_starts_with($telephone, '+')) {
            return $telephone;
        }

        // Handle phone numbers starting with '0' (local format)
        if (str_starts_with($telephone, '0')) {
            return '+234' . substr($telephone, 1);
        }

        // Handle phone numbers starting with '234' but without '+'
        if (str_starts_with($telephone, '234')) {
            return '+' . $telephone;
        }

        // Handle local Nigerian phone numbers (starting with 7, 8, or 9)
        // These are typically 10-digit numbers that should be prefixed with +234
        if (strlen($telephone) === 10 && preg_match('/^[789]\d{9}$/', $telephone)) {
            return '+234' . $telephone;
        }

        // Handle local Nigerian phone numbers that might be missing the leading 0
        // If it's 9 digits and starts with 7, 8, or 9, assume it needs +234 prefix
        if (strlen($telephone) === 9 && preg_match('/^[789]\d{8}$/', $telephone)) {
            return '+234' . $telephone;
        }

        // If none of the above patterns match, return as-is (assuming it's already internationalized)
        return '+' . $telephone;
    }
}
