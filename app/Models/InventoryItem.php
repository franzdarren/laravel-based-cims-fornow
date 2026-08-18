<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'item_code', 'item_name', 'item_category', 'uom_id',
        'supplier_id', 'reorder_threshold', 'reorder_qty', 'item_status',
    ];

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'uom_id', 'uom_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'item_id', 'item_id');
    }

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'item_id', 'item_id');
    }

    /**
     * Total on-hand quantity across all active batches (medicines/supplies only).
     */
    public function stockOnHand(): int
    {
        return (int) $this->batches()->where('batch_status', 'ACTIVE')->sum('quantity_on_hand');
    }

    public function isLowStock(): bool
    {
        if ($this->item_category === 'EQUIPMENT') {
            return false;
        }

        return $this->stockOnHand() <= $this->reorder_threshold;
    }

    public function hasActiveRecords(): bool
    {
        return $this->batches()->where('batch_status', 'ACTIVE')->where('quantity_on_hand', '>', 0)->exists()
            || $this->equipmentUnits()->where('equipment_status', '!=', 'DISPOSED')->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('item_status', 'active');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('item_category', strtoupper($category));
    }
}
