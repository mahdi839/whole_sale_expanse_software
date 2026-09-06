<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BankLedger
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

        BankTransaction::updateOrCreate(
            ['source_type' => $sourceType, 'source_id' => $sourceId],
            [
                'direction' => $direction,
                'type' => $type,
                'entry_type' => $attributes['entry_type'] ?? null,
                'amount' => $amount,
                'shop_id' => $attributes['shop_id'] ?? auth()->user()?->shop_id ?? Shop::orderBy('id')->value('id'),
                'date' => $attributes['date'] ?? Carbon::today()->toDateString(),
                'bank_name' => $attributes['bank_name'] ?? null,
                'bank_details' => $attributes['bank_details'] ?? null,
                'customer_id' => $attributes['customer_id'] ?? null,
                'supplier_id' => $attributes['supplier_id'] ?? null,
                'note' => $attributes['note'] ?? null,
            ]
        );
    }

    public function deleteSource(string $sourceType, int $sourceId): void
    {
        BankTransaction::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->get()
            ->each(function (BankTransaction $transaction) {
                if ($transaction->document) {
                    Storage::disk('public')->delete($transaction->document);
                }

                $transaction->delete();
            });
    }
}
