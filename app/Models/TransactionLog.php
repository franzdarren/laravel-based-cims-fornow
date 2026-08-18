<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The central, immutable ledger row for every posted stock-affecting
 * action: an approved receiving, an issuance, a disposal, or a manual
 * adjustment. Line-level detail (which batch/equipment, before/after
 * quantities and statuses) lives in TransactionLine.
 */
class TransactionLog extends Model
{
    protected $table = 'transaction_log';

    protected $primaryKey = 'transaction_id';

    public $timestamps = false;

    protected $fillable = [
        'transaction_type', 'reason', 'user_id', 'receiving_transaction_id',
        'reference_no', 'transaction_datetime',
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
    ];

    public const CANONICAL_TYPES = ['RECEIVING', 'DISPOSAL', 'ISSUANCE', 'ADJUSTMENT'];

    /**
     * A plain administrative audit note (user/role/supplier/item/settings
     * CRUD) — none of those are inventory-affecting, so they don't need
     * transaction_line rows, but they still need to show up on the
     * Transaction Log. Stored as a lineless ADJUSTMENT entry, which the
     * ERD's schema already allows without any further extension.
     */
    public static function note(?User $user, string $message, ?string $referenceNo = null): self
    {
        return static::create([
            'transaction_type' => 'ADJUSTMENT',
            'user_id' => $user?->user_id,
            'reference_no' => $referenceNo,
            'reason' => $message,
        ]);
    }

    public static function nextDisposalRef(): string
    {
        $prefix = 'DSP-'.now()->format('Y-md').'-';
        do {
            $candidate = $prefix.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (static::where('reference_no', $candidate)->exists());

        return $candidate;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function receivingTransaction(): BelongsTo
    {
        return $this->belongsTo(ReceivingTransaction::class, 'receiving_transaction_id', 'receiving_transaction_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(TransactionLine::class, 'transaction_id', 'transaction_id');
    }

    public function issuance(): HasOne
    {
        return $this->hasOne(IssuanceTransaction::class, 'transaction_id', 'transaction_id');
    }

    /**
     * A single human-readable line for the Transaction Log / dashboard
     * "recent activity" list, composed from this row's reason plus its
     * line-level detail (replaces the old free-text "detail" column).
     */
    public function summary(): string
    {
        if ($this->reason) {
            return $this->reason;
        }

        $lineText = $this->lines->map(fn (TransactionLine $l) => $l->describe())->filter()->implode('; ');

        return $lineText ?: ucfirst(strtolower($this->transaction_type)).' transaction.';
    }

    public function date()
    {
        return $this->transaction_datetime;
    }

    // The new schema's transaction_type is already the canonical 4-value
    // set (RECEIVING/DISPOSAL/ISSUANCE/ADJUSTMENT) — no raw->canonical
    // mapping needed anymore. Kept as a passthrough so views written
    // against the old normalized-type concept don't need to change.
    public function normalizedType(): string
    {
        return $this->transaction_type;
    }

    // Read-only alias so views/controllers written against the old
    // schema's `type`/`detail`/`date` column names keep working.
    public function getTypeAttribute()
    {
        return $this->transaction_type;
    }

    public function getDetailAttribute()
    {
        return $this->summary();
    }

    public function getDateAttribute()
    {
        return $this->transaction_datetime;
    }
}
