<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasurement extends Model
{
    protected $table = 'unit_of_measurement';

    protected $primaryKey = 'uom_id';

    protected $fillable = ['uom_name'];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'uom_id', 'uom_id');
    }
}
