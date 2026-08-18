<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $table = 'location';

    protected $primaryKey = 'location_id';

    protected $fillable = ['location_name'];

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'location_id', 'location_id');
    }

    public function receivingTransactionLines(): HasMany
    {
        return $this->hasMany(ReceivingTransactionLine::class, 'location_id', 'location_id');
    }
}
