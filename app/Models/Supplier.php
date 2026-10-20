<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'name_ur', 'contact_person', 'phone', 'email', 'address', 'city',
        'return_policy_days', 'response_time_days', 'payment_terms', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute(): string
    {
        return (app()->getLocale() === 'ur' && filled($this->name_ur)) ? $this->name_ur : $this->name;
    }
}