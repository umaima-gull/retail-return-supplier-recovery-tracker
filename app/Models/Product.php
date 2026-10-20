<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'supplier_id', 'sku', 'barcode', 'name', 'name_ur', 'unit',
        'cost_price', 'sale_price', 'stock_qty', 'reorder_level',
        'has_expiry', 'is_returnable', 'is_active',
    ];

    protected $casts = [
        'cost_price'    => 'decimal:2',
        'sale_price'    => 'decimal:2',
        'has_expiry'    => 'boolean',
        'is_returnable' => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    /** Urdu mode mein Urdu naam, na ho to English naam */
    public function getDisplayNameAttribute(): string
    {
        return (app()->getLocale() === 'ur' && filled($this->name_ur)) ? $this->name_ur : $this->name;
    }

    /** 'out' = ختم, 'low' = کم اسٹاک, 'ok' = دستیاب */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock_qty <= 0) {
            return 'out';
        }

        return $this->stock_qty <= $this->reorder_level ? 'low' : 'ok';
    }
}