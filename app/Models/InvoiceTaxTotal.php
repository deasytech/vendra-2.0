<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTaxTotal extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'tax_amount', 'tax_subtotal'];

    protected $casts = ['tax_subtotal' => 'array'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
