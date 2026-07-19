<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\Shop;
use Illuminate\Support\Carbon;

class CashLedger
{
    public function syncSource(
        string $sourceType,
        int $sourceId,
        string $direction,
        string $type,
        float $amount,
        array $attributes = []
    ): void {
        if ($amount <= 0) {
            $this->deleteSource($sourceType, $sourceId);
            return;
        }

        CashTransaction::updateOrCreate(
            ['source_type' => $sourceType, 'source_id' => $sourceId],
            [
                'direction' => $direction,
                'type' => $type,
                'amount' => $amount,
                'shop_id' => $attributes['shop_id'] ?? auth()->user()?->shop_id ?? Shop::orderBy('id')->value('id'),
                'date' => $attributes['date'] ?? Carbon::today()->toDateString(),
                'payment_method' => $attributes['payment_method'] ?? null,
                'customer_id' => $attributes['customer_id'] ?? null,
                'supplier_id' => $attributes['supplier_id'] ?? null,
                'note' => $attributes['note'] ?? null,
            ]
        );
    }

    public function deleteSource(string $sourceType, int $sourceId): void
    {
        CashTransaction::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }
}
