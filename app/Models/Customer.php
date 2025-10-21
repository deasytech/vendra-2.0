<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tin',
        'email',
        'phone',
        'business_description',
        'street_name',
        'city_name',
        'postal_zone',
        'state',
        'country',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function toPartyObject(): array
    {
        return [
            "party_name"           => $this->name,
            "tin"                  => $this->tin,
            "email"                => $this->email,
            "telephone"            => $this->normalizeTelephone($this->phone),
            "business_description" => $this->business_description,
            "postal_address" => [
                "street_name" => $this->street_name,
                "city_name"   => $this->city_name,
                "postal_zone" => $this->postal_zone,
                "country"     => $this->country ?? 'NG',
            ],
        ];
    }

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
