<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'hsn_code',
        'isic_code',
        'service_category',
        'product_category',
        'discount_rate',
        'discount_amount',
        'fee_rate',
        'fee_amount',
        'invoiced_quantity',
        'line_extension_amount',
        'item',
        'price',
        'order'
    ];

    protected $casts = [
        'item' => 'array',
        'price' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
