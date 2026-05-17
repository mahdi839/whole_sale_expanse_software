<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $dateFrom = $request->input('date_from', $today);
        $dateTo = $request->input('date_to', $today);
        $paymentStatus = $request->input('payment_status');
        $canViewProfit = auth()->user()->hasRole('Super Admin');
        $shopId = auth()->user()->canManageAllShops() ? null : (auth()->user()->shop_id ?: -1);

        // ── Base query scopes ────────────────────────────────────────
        $saleScope = function ($q) use ($dateFrom, $dateTo, $paymentStatus, $shopId) {
            $q->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
                ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));
        };

        $purchaseScope = function ($q) use ($dateFrom, $dateTo) {
            $q->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));
        };

        $expenseScope = function ($q) use ($dateFrom, $dateTo) {
            $q->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo));
        };

        $saleReturnScope = function ($q) use ($dateFrom, $dateTo, $shopId) {
            $q->where('return_status', 'approved')
                ->when($shopId, fn ($q) => $q->whereHas('sale', fn ($sale) => $sale->where('shop_id', $shopId)))
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));
        };

        $purchaseReturnScope = function ($q) use ($dateFrom, $dateTo) {
            $q->where('return_status', 'approved')
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));
        };

        // ── Summary cards ────────────────────────────────────────────
        $stockValue = Stock::query()
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->when($shopId, fn ($q) => $q->where('stocks.shop_id', $shopId), fn ($q) => $q->whereNull('stocks.shop_id'))
            ->selectRaw('
                COALESCE(SUM(stocks.stock_qty), 0) as total_stock_qty,
                COALESCE(SUM(stocks.stock_qty * COALESCE(products.purchase_price, 0)), 0) as cost_value,
                COALESCE(SUM(stocks.stock_qty * COALESCE(products.selling_price, 0)), 0) as retail_value
            ')
            ->first();

        $stats = [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_stock_qty' => (float) ($stockValue->total_stock_qty ?? 0),
            'total_stock_cost_value' => (float) ($stockValue->cost_value ?? 0),
            'total_stock_retail_value' => (float) ($stockValue->retail_value ?? 0),
            'total_sales' => Sale::where($saleScope)->sum('grand_total'),
            'total_sales_due' => Sale::where($saleScope)->sum('due'),
            'total_sale_returns' => SaleReturn::where($saleReturnScope)->sum('return_amount'),
            'total_purchases' => Purchase::where($purchaseScope)->sum('grand_total'),
            'total_purchase_returns' => PurchaseReturn::where($purchaseReturnScope)->sum('return_amount'),
            'total_purchase_due' => Purchase::where($purchaseScope)->sum('due_amount'),
            'total_expenses' => Expense::where($expenseScope)->sum('amount'),
            'total_item_profit' => 0,
            'net_profit' => 0,
        ];

        if ($canViewProfit) {
            $returnedQtySub = DB::table('sale_return_items')
                ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                ->where('sale_returns.return_status', 'approved')
                ->selectRaw('sale_return_items.sale_item_id, COALESCE(SUM(sale_return_items.qty), 0) as returned_qty')
                ->groupBy('sale_return_items.sale_item_id');

            $cogs = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->leftJoinSub($returnedQtySub, 'returned_items', 'returned_items.sale_item_id', '=', 'sale_items.id')
                ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
                ->when($dateFrom, fn ($q) => $q->whereDate('sales.created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('sales.created_at', '<=', $dateTo))
                ->when($paymentStatus, fn ($q) => $q->where('sales.payment_status', $paymentStatus))
                ->selectRaw('COALESCE(SUM((sale_items.qty - COALESCE(returned_items.returned_qty, 0)) * sale_items.cost_price), 0) as cogs')
                ->value('cogs');

            $netSales = (float) $stats['total_sales'] - (float) $stats['total_sale_returns'];
            $stats['total_item_profit'] = $netSales - (float) $cogs;

            $stats['net_profit'] =
                (float) $stats['total_item_profit']
                - (float) $stats['total_expenses'];
        }
        // ── Sales last 7 days OR within date range chart ─────────────
        $chartDays = 6;
        $chartStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->subDays($chartDays)->startOfDay();
        $chartEnd = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $salesChart = Sale::selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartData = [];
        $diff = $chartStart->diffInDays($chartEnd);
        $step = $diff > 30 ? (int) ceil($diff / 30) : 1;

        $cursor = $chartStart->copy();

        while ($cursor->lte($chartEnd)) {
            $date = $cursor->toDateString();
            $chartLabels[] = $cursor->format('d M');
            $chartData[] = (float) ($salesChart[$date] ?? 0);
            $cursor->addDays($step);
        }

        // ── Top 10 selling products ──────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('products.product_name, SUM(sale_items.qty) as total_qty, SUM(sale_items.line_total) as total_revenue')
            ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->when($dateFrom, fn ($q) => $q->whereDate('sales.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sales.created_at', '<=', $dateTo))
            ->when($paymentStatus, fn ($q) => $q->where('sales.payment_status', $paymentStatus))
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ── Top 7 customers ──────────────────────────────────────────
        if ($dateFrom || $dateTo || $paymentStatus) {
            $topCustomers = DB::table('sales')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->selectRaw('customers.full_name, SUM(sales.grand_total) as total_sale, SUM(sales.paid) as total_paid, SUM(sales.due) as due')
                ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
                ->when($dateFrom, fn ($q) => $q->whereDate('sales.created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('sales.created_at', '<=', $dateTo))
                ->when($paymentStatus, fn ($q) => $q->where('sales.payment_status', $paymentStatus))
                ->groupBy('customers.id', 'customers.full_name')
                ->orderByDesc('total_sale')
                ->limit(7)
                ->get();
        } elseif (! $shopId) {
            $topCustomers = Customer::orderByDesc('total_sale')
                ->limit(7)
                ->get(['full_name', 'total_sale', 'total_paid', 'due']);
        } else {
            $topCustomers = DB::table('sales')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->where('sales.shop_id', $shopId)
                ->selectRaw('customers.full_name, SUM(sales.grand_total) as total_sale, SUM(sales.paid) as total_paid, SUM(sales.due) as due')
                ->groupBy('customers.id', 'customers.full_name')
                ->orderByDesc('total_sale')
                ->limit(7)
                ->get();
        }

        // ── Low stock alerts ─────────────────────────────────────────
        $lowStock = Stock::with('product')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId), fn ($q) => $q->whereNull('shop_id'))
            ->where('stock_qty', '<=', 10)
            ->orderBy('stock_qty')
            ->limit(6)
            ->get();

        // ── Recent 10 sales ──────────────────────────────────────────
        $recentSales = Sale::with(['customer', 'items'])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->latest()
            ->limit(10)
            ->get();

        $filters = compact('dateFrom', 'dateTo', 'paymentStatus');

        return view('dashboard', compact(
            'stats',
            'chartLabels',
            'chartData',
            'topProducts',
            'topCustomers',
            'lowStock',
            'recentSales',
            'filters',
            'canViewProfit',
        ));
    }
}
