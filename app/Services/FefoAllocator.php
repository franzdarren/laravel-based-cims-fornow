<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * First-Expiry, First-Out allocation for medicine/supply issuance.
 *
 * Given a quantity to issue, picks stock from the batch expiring soonest
 * first. If that batch doesn't have enough, the remainder is pulled from
 * the next-soonest-expiring batch, and so on — exactly as described in the
 * user manual (section 5.3.2).
 */
class FefoAllocator
{
    /**
     * @return Collection<int, array{batch: Batch, qty: int}>
     *
     * @throws RuntimeException when total on-hand stock is insufficient.
     */
    public static function preview(InventoryItem $item, int $qtyNeeded): Collection
    {
        ['allocations' => $allocations, 'short' => $short] = static::previewWithShortfall($item, $qtyNeeded);

        if ($short > 0) {
            throw new RuntimeException("Insufficient stock for {$item->item_name}: short by {$short} {$item->uom?->uom_name}.");
        }

        return $allocations;
    }

    /**
     * Same greedy FEFO walk as preview(), but never throws — returns
     * whatever could be allocated plus the remaining shortfall (0 if none).
     * Used by the live allocation-preview endpoint, which needs to show a
     * partial/insufficient-stock state rather than erroring.
     *
     * @return array{allocations: Collection<int, array{batch: Batch, qty: int}>, short: int}
     */
    public static function previewWithShortfall(InventoryItem $item, int $qtyNeeded): array
    {
        $remaining = max(0, $qtyNeeded);
        $allocations = collect();

        $batches = Batch::query()
            ->where('item_id', $item->item_id)
            ->active()
            ->where('quantity_on_hand', '>', 0)
            ->fefoOrder()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, $batch->quantity_on_hand);
            $allocations->push(['batch' => $batch, 'qty' => $take]);
            $remaining -= $take;
        }

        return ['allocations' => $allocations, 'short' => $remaining];
    }

    /**
     * Applies the allocation: deducts quantity_on_hand from each batch used
     * (marking a batch Inactive once it hits zero) and returns the same
     * batch => qty breakdown for building transaction lines / receipts.
     *
     * @return Collection<int, array{batch: Batch, qty: int}>
     */
    public static function allocateAndDeduct(InventoryItem $item, int $qtyNeeded): Collection
    {
        $allocations = static::preview($item, $qtyNeeded);

        foreach ($allocations as $allocation) {
            $batch = $allocation['batch'];
            $batch->quantity_on_hand -= $allocation['qty'];
            if ($batch->quantity_on_hand <= 0) {
                $batch->quantity_on_hand = 0;
                $batch->batch_status = 'INACTIVE';
            }
            $batch->save();
        }

        return $allocations;
    }
}
