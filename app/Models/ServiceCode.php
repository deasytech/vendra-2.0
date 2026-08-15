<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCode extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];
}
