<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $primaryKey = 'equipment_id';

    protected $fillable = [
        'item_id', 'receive_transaction_id', 'asset_tag', 'serial_number',
        'brand', 'model', 'location_id', 'equipment_status',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id');
    }

    public function receivingTransaction(): BelongsTo
    {
        return $this->belongsTo(ReceivingTransaction::class, 'receive_transaction_id', 'receiving_transaction_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
}
