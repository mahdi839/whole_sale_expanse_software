<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductSalesReport
{
    public const SORTS = [
        'product_name' => 'Product',
        'sales_count' => 'Sales Count',
        'sold_qty' => 'Sold Qty',
        'return_qty' => 'Return Qty',
        'net_qty' => 'Net Qty',
        'sales_amount' => 'Sales Amount',
        'return_amount' => 'Return Amount',
        'net_amount' => 'Net Amount',
        'return_rate' => 'Return %',
        'stock_qty' => 'Stock',
    ];

    public function filters(Request $request, User $user): array
    {
        $range = $request->input('range');
        if (! in_array($range, ['today', '7d', 'month', 'year', 'all', 'custom'], true)) {
            $range = $request->filled('date_from') || $request->filled('date_to') ? 'custom' : 'month';
        }

        [$dateFrom, $dateTo] = $this->datesForRange($range, $request->input('date_from'), $request->input('date_to'));

        $sort = $request->input('sort', 'sales_amount');
        if (! array_key_exists($sort, self::SORTS)) {
            $sort = 'sales_amount';
        }

        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        if ($sort === 'product_name' && ! $request->filled('dir')) {
            $dir = 'asc';
        }

        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, [25, 50, 100], true)) {
            $perPage = 25;
        }

        return [
            'search' => trim((string) $request->input('search', '')),
            'shop_id' => $user->canManageAllShops() ? $request->input('shop_id') : null,
            'payment_status' => $request->input('payment_status'),
            'sale_status' => $request->input('sale_status'),
            'range' => $range,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'activity' => $request->input('activity', 'sold') === 'all' ? 'all' : 'sold',
            'sort' => $sort,
            'dir' => $dir,
            'per_page' => $perPage,
        ];
    }

    public function shops(User $user): Collection
    {
        if (! $user->canManageAllShops()) {
            return collect();
        }

        return Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function paginate(array $filters, User $user): LengthAwarePaginator
    {
        return $this->applySort($this->selectColumns($this->joinedQuery($filters, $user), $filters, $user), $filters)
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    public function rows(array $filters, User $user): Collection
    {
        return $this->applySort($this->selectColumns($this->joinedQuery($filters, $user), $filters, $user), $filters)
            ->get();
    }

    public function totals(array $filters, User $user): object
    {
        $row = $this->joinedQuery($filters, $user)
            ->toBase()
            ->reorder()
            ->selectRaw('
                COUNT(products.id) as products_count,
                COALESCE(SUM(COALESCE(sales_agg.sales_count, 0)), 0) as sales_count,
                COALESCE(SUM(COALESCE(sales_agg.sold_qty, 0)), 0) as sold_qty,
                COALESCE(SUM(COALESCE(sales_agg.sales_amount, 0)), 0) as sales_amount,
                COALESCE(SUM(COALESCE(returns_agg.return_qty, 0)), 0) as return_qty,
                COALESCE(SUM(COALESCE(returns_agg.return_amount, 0)), 0) as return_amount
            ')
            ->first();

        $soldQty = (float) ($row->sold_qty ?? 0);
        $salesAmount = (float) ($row->sales_amount ?? 0);
        $returnQty = (float) ($row->return_qty ?? 0);
        $returnAmount = (float) ($row->return_amount ?? 0);

        return (object) [
            'products_count' => (int) ($row->products_count ?? 0),
            'sales_count' => (int) ($row->sales_count ?? 0),
            'sold_qty' => $soldQty,
            'sales_amount' => $salesAmount,
            'return_qty' => $returnQty,
            'return_amount' => $returnAmount,
            'net_qty' => $soldQty - $returnQty,
            'net_amount' => $salesAmount - $returnAmount,
            'return_rate' => $soldQty > 0 ? ($returnQty / $soldQty) * 100 : 0,
        ];
    }

    public function periodLabel(array $filters): string
    {
        if ($filters['range'] === 'all' || (! $filters['date_from'] && ! $filters['date_to'])) {
            return 'All time';
        }

        $from = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') : '…';
        $to = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') : 'today';

        return $from.' – '.$to;
    }

    public function shopLabel(array $filters, User $user, Collection $shops): string
    {
        if (! $user->canManageAllShops()) {
            return $user->shop?->name ?: 'Assigned shop';
        }

        if ($filters['shop_id']) {
            return $shops->firstWhere('id', (int) $filters['shop_id'])?->name ?: 'Selected shop';
        }

        return 'All shops';
    }

    private function datesForRange(string $range, ?string $from, ?string $to): array
    {
        return match ($range) {
            'today' => [now()->toDateString(), now()->toDateString()],
            '7d' => [now()->subDays(6)->toDateString(), now()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->toDateString()],
            'all' => [null, null],
            default => [$from ?: null, $to ?: null],
        };
    }

    private function joinedQuery(array $filters, User $user): Builder
    {
        $shopId = $this->resolvedShopId($user, $filters);

        $salesSub = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->when($filters['payment_status'], fn ($q) => $q->where('sales.payment_status', $filters['payment_status']))
            ->when($filters['sale_status'], fn ($q) => $q->where('sales.status', $filters['sale_status']))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('sales.created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('sales.created_at', '<=', $filters['date_to']))
            ->groupBy('sale_items.product_id')
            ->selectRaw('
                sale_items.product_id,
                COUNT(DISTINCT sales.id) as sales_count,
                COALESCE(SUM(sale_items.qty), 0) as sold_qty,
                COALESCE(SUM(sale_items.line_total), 0) as sales_amount
            ');

        $returnsSub = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->leftJoin('sales', 'sales.id', '=', 'sale_returns.sale_id')
            ->where('sale_returns.return_status', 'approved')
            ->when($shopId !== null, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->when($filters['payment_status'], fn ($q) => $q->where('sales.payment_status', $filters['payment_status']))
            ->when($filters['sale_status'], fn ($q) => $q->where('sales.status', $filters['sale_status']))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('sale_returns.created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('sale_returns.created_at', '<=', $filters['date_to']))
            ->groupBy('sale_return_items.product_id')
            ->selectRaw('
                sale_return_items.product_id,
                COALESCE(SUM(sale_return_items.qty), 0) as return_qty,
                COALESCE(SUM(sale_return_items.line_total), 0) as return_amount
            ');

        return Product::query()
            ->leftJoinSub($salesSub, 'sales_agg', 'sales_agg.product_id', '=', 'products.id')
            ->leftJoinSub($returnsSub, 'returns_agg', 'returns_agg.product_id', '=', 'products.id')
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($sub) use ($search) {
                    $sub->where('products.product_name', 'like', "%{$search}%")
                        ->orWhere('products.sku', 'like', "%{$search}%")
                        ->orWhere('products.product_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['activity'] !== 'all', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('sales_agg.sold_qty', '>', 0)
                        ->orWhere('returns_agg.return_qty', '>', 0);
                });
            });
    }

    private function selectColumns(Builder $query, array $filters, User $user): Builder
    {
        $shopId = $this->resolvedShopId($user, $filters);
        $stockSql = $shopId !== null
            ? 'COALESCE((SELECT SUM(stocks.stock_qty) FROM stocks WHERE stocks.product_id = products.id AND stocks.shop_id = '.(int) $shopId.'), 0) as stock_qty'
            : 'COALESCE((SELECT SUM(stocks.stock_qty) FROM stocks WHERE stocks.product_id = products.id), 0) as stock_qty';

        return $query->select([
            'products.id',
            'products.product_name',
            'products.sku',
            'products.product_code',
        ])->selectRaw('
            COALESCE(sales_agg.sales_count, 0) as sales_count,
            COALESCE(sales_agg.sold_qty, 0) as sold_qty,
            COALESCE(sales_agg.sales_amount, 0) as sales_amount,
            COALESCE(returns_agg.return_qty, 0) as return_qty,
            COALESCE(returns_agg.return_amount, 0) as return_amount,
            COALESCE(sales_agg.sold_qty, 0) - COALESCE(returns_agg.return_qty, 0) as net_qty,
            COALESCE(sales_agg.sales_amount, 0) - COALESCE(returns_agg.return_amount, 0) as net_amount,
            CASE WHEN COALESCE(sales_agg.sold_qty, 0) > 0
                THEN COALESCE(sales_agg.sales_amount, 0) / sales_agg.sold_qty
                ELSE 0 END as avg_price,
            CASE WHEN COALESCE(sales_agg.sold_qty, 0) > 0
                THEN (COALESCE(returns_agg.return_qty, 0) / sales_agg.sold_qty) * 100
                ELSE 0 END as return_rate
        ')->selectRaw($stockSql);
    }

    private function applySort(Builder $query, array $filters): Builder
    {
        $column = $filters['sort'] === 'product_name' ? 'products.product_name' : $filters['sort'];

        return $query->orderBy($column, $filters['dir'])->orderBy('products.product_name');
    }

    private function resolvedShopId(User $user, array $filters): ?int
    {
        if (! $user->canManageAllShops()) {
            return $user->shop_id ?: -1;
        }

        return filled($filters['shop_id']) ? (int) $filters['shop_id'] : null;
    }
}
