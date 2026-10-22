<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'mfg_date'    => 'date',
        'expiry_date' => 'date',
        'received_at' => 'date',
    ];
}