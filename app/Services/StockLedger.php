<?php

namespace App\Services;

use App\Models\StockMovement;

class StockLedger
{
    public function record(
        int $productId,
        ?int $shopId,
        string $type,
        string $direction,
        float $qty,
        array $meta = []
    ): void {
        if ($qty <= 0) {
            return;
        }

        StockMovement::create([
            'product_id' => $productId,
            'shop_id' => $shopId,
            'movement_type' => $type,
            'direction' => $direction,
            'qty' => $qty,
            'reference' => $meta['reference'] ?? null,
            'note' => $meta['note'] ?? null,
            'source_type' => $meta['source_type'] ?? null,
            'source_id' => $meta['source_id'] ?? null,
            'link_type' => $meta['link_type'] ?? null,
            'link_id' => $meta['link_id'] ?? null,
            'user_id' => $meta['user_id'] ?? auth()->id(),
            'occurred_at' => $meta['occurred_at'] ?? now(),
        ]);
    }

    public function recordQuantityChange(
        int $productId,
        ?int $shopId,
        float $before,
        float $after,
        array $meta = []
    ): void {
        $delta = round($after - $before, 2);

        if (abs($delta) < 0.01) {
            return;
        }

        if ($before <= 0 && $delta > 0) {
            $this->record($productId, $shopId, 'add_stock', 'in', $delta, $meta);

            return;
        }

        $this->record(
            $productId,
            $shopId,
            'adjustment',
            $delta > 0 ? 'in' : 'out',
            abs($delta),
            $meta
        );
    }
}
