<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpiryRecord extends Model
{
    public const ACTIONS     = ['pending', 'discounted', 'claim_raised', 'recovered', 'disposed'];
    public const RESOLVED    = ['recovered', 'disposed'];
    public const URGENT_DAYS = 3;   // itne din ya kam bachay hon to "urgent"
    public const SOON_DAYS   = 7;   // itne din ya kam bachay hon to "jald ekspire"

    protected $fillable = [
        'product_id', 'supplier_id', 'qty', 'expiry_date',
        'action_status', 'loss_value', 'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'loss_value'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Purane flags bhi theek rakhta hai (asli hisaab expiry_date se hota hai)
        static::saving(function (self $m) {
            $d = $m->expiry_date->copy()->startOfDay();
            $m->is_expired = $d->lt(today());
            $m->is_urgent  = $d->lte(today()->addDays(self::URGENT_DAYS));
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(SupplierClaim::class, 'claim_id');
    }

    /** + = ekspire hone mein baqi din, - = ekspire hue itne din ho gaye */
    public function getDaysLeftAttribute(): int
    {
        return (int) today()->diffInDays($this->expiry_date->copy()->startOfDay(), false);
    }

    /** expired | urgent | soon | ok */
    public function getStateAttribute(): string
    {
        $d = $this->days_left;

        return $d < 0 ? 'expired' : ($d <= self::URGENT_DAYS ? 'urgent' : ($d <= self::SOON_DAYS ? 'soon' : 'ok'));
    }
}