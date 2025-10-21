<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTransmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'irn',
        'action',
        'request_payload',
        'response_payload',
        'status',
        'message',
        'error',
        'transmitted_at'
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'transmitted_at' => 'datetime'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
