<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_id',
        'invoice_reference',
        'irn',
        'issue_date',
        'due_date',
        'invoice_type_code',
        'document_currency_code',
        'payment_status',
        'note',
        'payment_terms_note',
        'accounting_supplier_party',
        'accounting_customer_party',
        'legal_monetary_total',
        'metadata',
        'transmit',
        'delivered'
    ];

    protected $casts = [
        'note' => 'array',
        'payment_terms_note' => 'array',
        'accounting_supplier_party' => 'array',
        'accounting_customer_party' => 'array',
        'legal_monetary_total' => 'array',
        'metadata' => 'array',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function taxTotals()
    {
        return $this->hasMany(InvoiceTaxTotal::class);
    }

    public function transmissions()
    {
        return $this->hasMany(InvoiceTransmission::class);
    }

    public function attachments()
    {
        return $this->hasMany(InvoiceAttachment::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
