<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ReceivingTransaction extends Model
{
    protected $table = 'receiving_transaction';

    protected $primaryKey = 'receiving_transaction_id';

    protected $fillable = [
        'ref_no', 'supplier_id', 'received_by', 'approved_by',
        'date_received', 'remarks', 'status', 'return_reason', 'cancel_reason', 'decided_at',
    ];

    protected $casts = [
        'date_received' => 'date',
        'decided_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReceivingTransactionLine::class, 'receiving_transaction_id', 'receiving_transaction_id');
    }

    public static function nextReferenceNo(): string
    {
        do {
            $candidate = 'DR-'.random_int(10000, 99999);
        } while (static::where('ref_no', $candidate)->exists());

        return $candidate;
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Reasons this request cannot be approved yet — every line needs a
     * location, and every equipment line needs exactly one unit with an
     * asset tag and serial number.
     */
    public function approvalBlockers(): array
    {
        $blockers = [];

        if ($this->lines->contains(fn (ReceivingTransactionLine $l) => empty($l->location_id))) {
            $blockers[] = 'Every item requires a location.';
        }

        if ($this->lines->contains(fn (ReceivingTransactionLine $l) => $l->item->item_category === 'EQUIPMENT'
            && (empty($l->asset_tag) || empty($l->serial_number) || (int) $l->quantity !== 1))) {
            $blockers[] = 'Equipment requires one unit per line with an asset tag and serial number.';
        }

        return $blockers;
    }

    /**
     * Supervisor approval: posts every line to inventory (creates/updates
     * batch records for medicines & supplies, creates equipment unit
     * records for equipment lines), writes one posted transaction_log
     * (RECEIVING) row with a transaction_line per batch/equipment touched,
     * then marks the request Approved.
     */
    public function approve(User $approver): void
    {
        DB::transaction(function () use ($approver) {
            $log = TransactionLog::create([
                'transaction_type' => 'RECEIVING',
                'user_id' => $approver->user_id,
                'receiving_transaction_id' => $this->id,
                'reference_no' => $this->ref_no,
                'reason' => null,
            ]);

            foreach ($this->lines as $line) {
                $item = $line->item;

                if ($item->item_category === 'EQUIPMENT') {
                    $equipment = Equipment::create([
                        'item_id' => $item->item_id,
                        'receive_transaction_id' => $this->id,
                        'asset_tag' => $line->asset_tag,
                        'serial_number' => $line->serial_number,
                        'brand' => $line->brand,
                        'model' => $line->model,
                        'location_id' => $line->location_id,
                        'equipment_status' => 'AVAILABLE',
                    ]);

                    $log->lines()->create([
                        'equipment_id' => $equipment->equipment_id,
                        'qty_before' => 0,
                        'qty_after' => 1,
                        'status_before' => null,
                        'status_after' => 'AVAILABLE',
                        'line_remarks' => "{$item->item_name}: received 1 unit (asset {$line->asset_tag})",
                    ]);
                } else {
                    $batch = Batch::where('item_id', $item->item_id)
                        ->where('batch_no', $line->batch_no)
                        ->first();

                    if ($batch) {
                        $before = $batch->quantity_on_hand;
                        $batch->quantity_received += $line->quantity;
                        $batch->quantity_on_hand += $line->quantity;
                        $batch->batch_status = 'ACTIVE';
                        $batch->save();
                    } else {
                        $before = 0;
                        $batch = Batch::create([
                            'item_id' => $item->item_id,
                            'receive_transaction_id' => $this->id,
                            'batch_no' => $line->batch_no,
                            'brand' => $line->brand,
                            'expiry_date' => $line->expiry_date,
                            'quantity_received' => $line->quantity,
                            'quantity_on_hand' => $line->quantity,
                            'batch_status' => 'ACTIVE',
                        ]);
                    }

                    $log->lines()->create([
                        'batch_id' => $batch->batch_id,
                        'qty_before' => $before,
                        'qty_after' => $batch->quantity_on_hand,
                        'status_before' => 'ACTIVE',
                        'status_after' => 'ACTIVE',
                        'line_remarks' => "{$item->item_name} (batch {$batch->batch_no}): stock {$before} → {$batch->quantity_on_hand} {$item->uom?->uom_name}",
                    ]);
                }
            }

            $this->update([
                'status' => 'APPROVED',
                'approved_by' => $approver->user_id,
                'decided_at' => now(),
            ]);
        });
    }

    public function returnWithReason(User $supervisor, string $reason): void
    {
        $this->update([
            'status' => 'RETURNED',
            'return_reason' => $reason,
            'decided_at' => now(),
        ]);

        TransactionLog::create([
            'transaction_type' => 'ADJUSTMENT',
            'user_id' => $supervisor->user_id,
            'receiving_transaction_id' => $this->id,
            'reference_no' => $this->ref_no,
            'reason' => "Returned receiving transaction {$this->ref_no}: {$reason}",
        ]);
    }

    public function cancel(User $nurse, string $reason): void
    {
        $this->update([
            'status' => 'CANCELLED',
            'cancel_reason' => $reason,
            'decided_at' => now(),
        ]);

        TransactionLog::create([
            'transaction_type' => 'ADJUSTMENT',
            'user_id' => $nurse->user_id,
            'receiving_transaction_id' => $this->id,
            'reference_no' => $this->ref_no,
            'reason' => "Cancelled pending receiving transaction {$this->ref_no}: {$reason}",
        ]);
    }

    // Convenience accessor so existing code/views can keep reading
    // ->id / ->reference_no without churning every call site.
    public function getIdAttribute()
    {
        return $this->attributes['receiving_transaction_id'] ?? null;
    }

    public function getReferenceNoAttribute()
    {
        return $this->attributes['ref_no'] ?? null;
    }
}
