<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityCode extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];
}
