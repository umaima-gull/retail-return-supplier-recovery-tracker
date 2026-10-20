<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'name_ur', 'description'];

    public function getDisplayNameAttribute(): string
    {
        return (app()->getLocale() === 'ur' && filled($this->name_ur)) ? $this->name_ur : $this->name;
    }
}