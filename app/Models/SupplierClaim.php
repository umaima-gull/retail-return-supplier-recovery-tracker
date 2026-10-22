<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierClaim extends Model
{
    use SoftDeletes;

    public const STATUSES    = ['draft', 'pending', 'in_progress', 'approved', 'rejected', 'completed'];
    public const RESOLUTIONS = ['refund', 'replacement', 'credit_note'];
    public const OPEN        = ['pending', 'in_progress'];

    protected $guarded = [];

    protected $casts = [
        'claim_date'     => 'date',
        'due_date'       => 'date',
        'total_value'    => 'decimal:2',
        'approved_value' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierClaimItem::class, 'claim_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ClaimStatusLog::class, 'claim_id')->orderByDesc('id');
    }

    public function recoveries(): HasMany
    {
        return $this->hasMany(Recovery::class, 'claim_id');
    }

    /** Supplier ka jawab dene ki aakhri tareekh guzar gayi? */
    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, self::OPEN, true)
            && $this->due_date
            && $this->due_date->lt(today());
    }

    /** RET-1025 ... agla claim number */
    public static function nextClaimNo(): string
    {
        $max = static::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(claim_no, 5) AS UNSIGNED)) as m')
            ->value('m');

        return 'RET-' . (((int) $max ?: 1000) + 1);
    }
}