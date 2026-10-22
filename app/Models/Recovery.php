<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recovery extends Model
{
    protected $guarded = [];

    protected $casts = [
        'received_date' => 'date',
        'amount'        => 'decimal:2',
    ];
}