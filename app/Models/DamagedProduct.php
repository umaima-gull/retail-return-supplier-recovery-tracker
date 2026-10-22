<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DamagedProduct extends Model
{
    use SoftDeletes;

    public const STAGES   = ['on_delivery', 'in_store', 'customer_return'];
    public const STATUSES = ['reported', 'claim_raised', 'recovered', 'written_off'];

    protected $fillable = [
        'product_id', 'supplier_id', 'qty', 'damage_stage', 'reason', 'loss_value',
        'photo_path', 'status', 'reported_at', 'notes',
    ];

    protected $casts = [
        'loss_value'  => 'decimal:2',
        'reported_at' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(SupplierClaim::class, 'claim_id');
    }

    /** Tasveer ka link (storage:link chalana zaroori hai) */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}