<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerReturn extends Model
{
    use SoftDeletes;

    public const REASONS    = ['damaged', 'expired', 'defective', 'wrong_item', 'quality_issue', 'other'];
    public const CONDITIONS = ['resalable', 'not_resalable'];
    public const METHODS    = ['cash', 'card', 'exchange', 'store_credit'];
    public const STATUSES   = ['received', 'claim_raised', 'closed'];

    protected $fillable = [
        'return_no', 'product_id', 'supplier_id', 'customer_name', 'customer_phone', 'invoice_no',
        'qty', 'reason', 'item_condition', 'refund_amount', 'refund_method',
        'status', 'returned_at', 'notes',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'returned_at'   => 'date',
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

    /** CR-0001, CR-0002 ... agla number */
    public static function nextReturnNo(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->value('return_no');
        $n    = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'CR-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}