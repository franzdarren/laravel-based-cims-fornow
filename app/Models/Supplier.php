<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $primaryKey = 'supplier_id';

    public $timestamps = false;

    protected $fillable = [
        'supplier_name', 'contact_person', 'contact_no', 'address', 'status',
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'supplier_id', 'supplier_id');
    }

    public function receivingTransactions(): HasMany
    {
        return $this->hasMany(ReceivingTransaction::class, 'supplier_id', 'supplier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Reasons this supplier cannot be soft-deleted right now: open receiving
     * requests, a recent (within 3 years) approved delivery, or an active
     * item with active stock that still names this supplier as preferred.
     */
    public function deletionBlockers(): array
    {
        $cutoff = now()->subYears(3);

        $openReceiving = $this->receivingTransactions()
            ->whereIn('status', ['PENDING', 'RETURNED'])
            ->get();

        $recentApproved = $this->receivingTransactions()
            ->where('status', 'APPROVED')
            ->whereNotNull('date_received')
            ->whereBetween('date_received', [$cutoff, now()])
            ->get();

        $activeItems = $this->inventoryItems()
            ->where('item_status', 'active')
            ->get()
            ->filter(fn (InventoryItem $item) => $item->stockOnHand() > 0 || $item->equipmentUnits()->where('equipment_status', '!=', 'DISPOSED')->exists())
            ->values();

        return compact('openReceiving', 'recentApproved', 'activeItems');
    }

    public function deletionBlockedMessage(): ?string
    {
        $blockers = $this->deletionBlockers();
        $parts = [];

        if ($blockers['openReceiving']->isNotEmpty()) {
            $count = $blockers['openReceiving']->count();
            $parts[] = "{$count} pending/returned receiving transaction".($count === 1 ? '' : 's');
        }
        if ($blockers['recentApproved']->isNotEmpty()) {
            $count = $blockers['recentApproved']->count();
            $parts[] = "{$count} approved receiving transaction".($count === 1 ? '' : 's')." within the past 3 years";
        }
        if ($blockers['activeItems']->isNotEmpty()) {
            $names = $blockers['activeItems']->take(3)->pluck('item_name')->implode(', ');
            $suffix = $blockers['activeItems']->count() > 3 ? '…' : '';
            $plural = $blockers['activeItems']->count() === 1 ? '' : 's';
            $parts[] = "active supplied item{$plural}: {$names}{$suffix}";
        }

        return $parts ? 'Supplier cannot be deleted because of '.implode('; ', $parts).'.' : null;
    }
}
