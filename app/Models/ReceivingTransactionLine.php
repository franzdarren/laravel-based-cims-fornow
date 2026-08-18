<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingTransactionLine extends Model
{
    protected $table = 'receiving_transaction_line';

    protected $primaryKey = 'receiving_transaction_line_id';

    protected $fillable = [
        'receiving_transaction_id', 'item_id', 'quantity', 'brand', 'batch_no',
        'expiry_date', 'model', 'serial_number', 'asset_tag', 'location_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function receivingTransaction(): BelongsTo
    {
        return $this->belongsTo(ReceivingTransaction::class, 'receiving_transaction_id', 'receiving_transaction_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
}
