<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLine extends Model
{
    protected $table = 'transaction_line';

    protected $primaryKey = 'line_id';

    public $timestamps = false;

    protected $fillable = [
        'transaction_id', 'batch_id', 'equipment_id', 'qty_before', 'qty_after',
        'quantity_issued', 'status_before', 'status_after', 'line_remarks',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionLog::class, 'transaction_id', 'transaction_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'batch_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'equipment_id');
    }

    public function describe(): string
    {
        if ($this->line_remarks) {
            return $this->line_remarks;
        }

        if ($this->batch) {
            return "{$this->batch->item?->item_name} (batch {$this->batch->batch_no}): {$this->qty_before} → {$this->qty_after}";
        }

        if ($this->equipment) {
            return "{$this->equipment->item?->item_name} ({$this->equipment->asset_tag}): {$this->status_before} → {$this->status_after}";
        }

        return '';
    }
}
