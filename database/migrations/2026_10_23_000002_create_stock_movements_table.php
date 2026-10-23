<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type')->index();
            $table->string('direction', 8)->index();
            $table->decimal('qty', 12, 2);
            $table->string('reference')->nullable()->index();
            $table->text('note')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('link_type')->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['shop_id', 'occurred_at']);
        });

        $this->backfillSales();
        $this->backfillSaleReturns();
        $this->backfillDistributions();
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }

    private function backfillSales(): void
    {
        $lastId = 0;

        while (true) {
            $rows = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.id', '>', $lastId)
                ->orderBy('sale_items.id')
                ->limit(200)
                ->get([
                    'sale_items.id',
                    'sale_items.product_id',
                    'sale_items.qty',
                    'sales.id as sale_id',
                    'sales.shop_id',
                    'sales.user_id',
                    'sales.reference',
                    'sales.created_at',
                ]);

            if ($rows->isEmpty()) {
                break;
            }

            $payload = [];
            foreach ($rows as $row) {
                $lastId = (int) $row->id;
                if ((float) $row->qty <= 0 || empty($row->shop_id)) {
                    continue;
                }

                $payload[] = $this->row(
                    (int) $row->product_id,
                    (int) $row->shop_id,
                    'sale',
                    'out',
                    (float) $row->qty,
                    $row->reference,
                    null,
                    'sale_item',
                    (int) $row->id,
                    'sale',
                    (int) $row->sale_id,
                    $row->user_id ? (int) $row->user_id : null,
                    $row->created_at
                );
            }

            $this->insert($payload);
        }
    }

    private function backfillSaleReturns(): void
    {
        $lastId = 0;

        while (true) {
            $rows = DB::table('sale_return_items')
                ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                ->leftJoin('sales as origin_sales', 'origin_sales.id', '=', 'sale_returns.sale_id')
                ->leftJoin('sales as applied_sales', 'applied_sales.id', '=', 'sale_returns.applied_sale_id')
                ->where('sale_returns.return_status', 'approved')
                ->where('sale_return_items.id', '>', $lastId)
                ->orderBy('sale_return_items.id')
                ->limit(200)
                ->get([
                    'sale_return_items.id',
                    'sale_return_items.product_id',
                    'sale_return_items.qty',
                    'sale_returns.id as return_id',
                    'sale_returns.reference',
                    'sale_returns.created_at',
                    DB::raw('COALESCE(origin_sales.shop_id, applied_sales.shop_id) as shop_id'),
                ]);

            if ($rows->isEmpty()) {
                break;
            }

            $payload = [];
            foreach ($rows as $row) {
                $lastId = (int) $row->id;
                if ((float) $row->qty <= 0 || empty($row->shop_id)) {
                    continue;
                }

                $payload[] = $this->row(
                    (int) $row->product_id,
                    (int) $row->shop_id,
                    'sale_return',
                    'in',
                    (float) $row->qty,
                    $row->reference,
                    'Return sale',
                    'sale_return_item',
                    (int) $row->id,
                    'sale_return',
                    (int) $row->return_id,
                    null,
                    $row->created_at
                );
            }

            $this->insert($payload);
        }
    }

    private function backfillDistributions(): void
    {
        $lastId = 0;

        while (true) {
            $rows = DB::table('stock_distribution_items')
                ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.stock_distribution_id')
                ->leftJoin('shops', 'shops.id', '=', 'stock_distributions.shop_id')
                ->where('stock_distribution_items.id', '>', $lastId)
                ->orderBy('stock_distribution_items.id')
                ->limit(200)
                ->get([
                    'stock_distribution_items.id',
                    'stock_distribution_items.product_id',
                    'stock_distribution_items.qty',
                    'stock_distributions.id as distribution_id',
                    'stock_distributions.shop_id',
                    'stock_distributions.status',
                    'stock_distributions.received_at',
                    'stock_distributions.received_by',
                    'stock_distributions.created_at',
                    'stock_distributions.updated_at',
                    'shops.name as shop_name',
                ]);

            if ($rows->isEmpty()) {
                break;
            }

            $payload = [];
            foreach ($rows as $row) {
                $lastId = (int) $row->id;
                if ((float) $row->qty <= 0) {
                    continue;
                }

                $reference = 'DST-'.str_pad((string) $row->distribution_id, 6, '0', STR_PAD_LEFT);
                $shopName = $row->shop_name ?: 'shop';

                $payload[] = $this->row(
                    (int) $row->product_id,
                    null,
                    'distribution',
                    'out',
                    (float) $row->qty,
                    $reference,
                    'Sent to '.$shopName,
                    'stock_distribution_item',
                    (int) $row->id,
                    'stock_distribution',
                    (int) $row->distribution_id,
                    null,
                    $row->created_at
                );

                if ($row->status === 'received' && ! empty($row->shop_id)) {
                    $payload[] = $this->row(
                        (int) $row->product_id,
                        (int) $row->shop_id,
                        'distribution_receive',
                        'in',
                        (float) $row->qty,
                        $reference,
                        'Received into '.$shopName,
                        'stock_distribution_item',
                        (int) $row->id,
                        'stock_distribution',
                        (int) $row->distribution_id,
                        $row->received_by ? (int) $row->received_by : null,
                        $row->received_at ?: $row->updated_at
                    );
                }

                if ($row->status === 'cancelled') {
                    $payload[] = $this->row(
                        (int) $row->product_id,
                        null,
                        'distribution_cancel',
                        'in',
                        (float) $row->qty,
                        $reference,
                        'Returned to central inventory',
                        'stock_distribution_item',
                        (int) $row->id,
                        'stock_distribution',
                        (int) $row->distribution_id,
                        $row->received_by ? (int) $row->received_by : null,
                        $row->received_at ?: $row->updated_at
                    );
                }
            }

            $this->insert($payload);
        }
    }

    private function row(
        int $productId,
        ?int $shopId,
        string $type,
        string $direction,
        float $qty,
        ?string $reference,
        ?string $note,
        string $sourceType,
        int $sourceId,
        string $linkType,
        int $linkId,
        ?int $userId,
        mixed $occurredAt
    ): array {
        $now = now();

        return [
            'product_id' => $productId,
            'shop_id' => $shopId,
            'movement_type' => $type,
            'direction' => $direction,
            'qty' => $qty,
            'reference' => $reference,
            'note' => $note,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'link_type' => $linkType,
            'link_id' => $linkId,
            'user_id' => $userId,
            'occurred_at' => $occurredAt ?: $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function insert(array $payload): void
    {
        foreach (array_chunk($payload, 40) as $chunk) {
            DB::table('stock_movements')->insert($chunk);
        }
    }
};
