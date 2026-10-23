<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'shop_id',
        'movement_type',
        'direction',
        'qty',
        'reference',
        'note',
        'source_type',
        'source_id',
        'link_type',
        'link_id',
        'user_id',
        'occurred_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function filterOptions(): array
    {
        return [
            'sale' => 'Sale',
            'sale_return' => 'Return sale',
            'add_stock' => 'Add stock',
            'adjustment' => 'Stock adjustment',
            'transfer' => 'Transfer',
            'distribution' => 'Distribution',
        ];
    }

    public static function typesForFilter(string $filter): array
    {
        return match ($filter) {
            'sale' => ['sale', 'sale_reversal'],
            'sale_return' => ['sale_return', 'sale_return_reversal'],
            'add_stock' => ['add_stock'],
            'adjustment' => ['adjustment'],
            'transfer' => ['transfer'],
            'distribution' => ['distribution', 'distribution_receive', 'distribution_cancel'],
            default => [],
        };
    }

    public function label(): string
    {
        return match ($this->movement_type) {
            'sale' => 'Sale',
            'sale_reversal' => 'Sale reversed',
            'sale_return' => 'Return sale',
            'sale_return_reversal' => 'Return reversed',
            'add_stock' => 'Add stock',
            'adjustment' => 'Stock adjustment',
            'transfer' => $this->direction === 'in' ? 'Transfer in' : 'Transfer out',
            'distribution' => 'Distribute stock',
            'distribution_receive' => 'Stock received',
            'distribution_cancel' => 'Distribution cancelled',
            default => ucfirst(str_replace('_', ' ', (string) $this->movement_type)),
        };
    }

    public function locationLabel(): string
    {
        return $this->shop?->displayLabel() ?: 'Central';
    }

    public function detailNote(): ?string
    {
        $shop = $this->relationLoaded('linkedDistribution')
            ? $this->getRelation('linkedDistribution')?->shop
            : null;

        if ($shop && $this->movement_type === 'distribution') {
            return 'Sent to '.$shop->displayLabel();
        }

        if ($shop && $this->movement_type === 'distribution_receive') {
            return 'Received into '.$shop->displayLabel();
        }

        return $this->note;
    }

    public function linkUrl(): ?string
    {
        $user = auth()->user();

        if (! $user || ! $this->link_id) {
            return null;
        }

        if ($this->link_type === 'sale' && ($user->can('manage sales') || $user->can('view sales'))) {
            return route('sales.show', $this->link_id);
        }

        if ($this->link_type === 'sale_return' && ($user->can('manage sale returns') || $user->can('view sale returns'))) {
            return route('sale-returns.show', $this->link_id);
        }

        if ($this->link_type === 'stock_distribution' && $user->can('distribute stock')) {
            return route('stocks.distribute');
        }

        return null;
    }
}
