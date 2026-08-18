<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    protected $table = 'batch';

    protected $primaryKey = 'batch_id';

    protected $fillable = [
        'item_id', 'receive_transaction_id', 'batch_no',
        'brand', 'expiry_date', 'quantity_received', 'quantity_on_hand', 'batch_status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id');
    }

    public function receivingTransaction(): BelongsTo
    {
        return $this->belongsTo(ReceivingTransaction::class, 'receive_transaction_id', 'receiving_transaction_id');
    }

    /**
     * Near-expiry uses ONE global day-count that applies to every medicine
     * batch (System Settings > Near-Expiry Days). No per-item override.
     */
    public function isNearExpiry(): bool
    {
        if (! $this->expiry_date || $this->batch_status !== 'ACTIVE') {
            return false;
        }

        $today = Carbon::today();

        if ($today->greaterThan($this->expiry_date)) {
            return false;
        }

        $days = (int) Setting::get('near_expiry_days', 90);

        return $today->diffInDays($this->expiry_date) <= $days;
    }

    public function scopeActive($query)
    {
        return $query->where('batch_status', 'ACTIVE');
    }

    /**
     * Batches with stock, earliest expiry first — the basis for FEFO.
     * Batches without an expiry date (rare) are treated as expiring last.
     */
    public function scopeFefoOrder($query)
    {
        return $query->orderByRaw('expiry_date IS NULL, expiry_date ASC');
    }
}
