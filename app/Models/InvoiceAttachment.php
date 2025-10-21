<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'filename', 'path', 'mime', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
