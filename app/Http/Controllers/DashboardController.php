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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $paymentStatus = $request->input('payment_status');
        $shopId        = auth()->user()->canManageAllShops() ? null : (auth()->user()->shop_id ?: -1);

        // ── Base query scopes ────────────────────────────────────────
        $saleScope = function ($q) use ($dateFrom, $dateTo, $paymentStatus, $shopId) {
            $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->when($paymentStatus, fn($q) => $q->where('payment_status', $paymentStatus))
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId));
        };

        $purchaseScope = function ($q) use ($dateFrom, $dateTo) {
            $q->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo));
        };

        $expenseScope = function ($q) use ($dateFrom, $dateTo) {
            $q->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('date', '<=', $dateTo));
        };

        $saleReturnScope = function ($q) use ($dateFrom, $dateTo, $shopId) {
            $q->where('return_status', 'approved')
                ->when($shopId, fn($q) => $q->whereHas('sale', fn($sale) => $sale->where('shop_id', $shopId)))
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo));
        };

        $purchaseReturnScope = function ($q) use ($dateFrom, $dateTo) {
            $q->where('return_status', 'approved')
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo));
        };

        // ── Summary cards ────────────────────────────────────────────
        $stats = [
            'total_products'         => Product::count(),
            'total_customers'        => Customer::count(),
            'total_sales'            => Sale::where($saleScope)->sum('grand_total'),
            'total_sales_due'        => Sale::where($saleScope)->sum('due'),
            'total_sale_returns'     => SaleReturn::where($saleReturnScope)->sum('return_amount'),
            'total_purchases'        => Purchase::where($purchaseScope)->sum('grand_total'),
            'total_purchase_returns' => PurchaseReturn::where($purchaseReturnScope)->sum('return_amount'),
            'total_purchase_due'     => Purchase::where($purchaseScope)->sum('due_amount'),
            'total_expenses'         => Expense::where($expenseScope)->sum('amount'),
            'total_item_profit' => 0,
            'net_profit'        => 0,
        ];

        $totalItemProfit = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when($shopId, fn($q) => $q->where('sales.shop_id', $shopId))
            ->when($dateFrom, fn($q) => $q->whereDate('sales.created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('sales.created_at', '<=', $dateTo))
            ->when($paymentStatus, fn($q) => $q->where('sales.payment_status', $paymentStatus))
            ->selectRaw('COALESCE(SUM(sale_items.line_profit), 0) as total_profit')
            ->value('total_profit');

        $stats['total_item_profit'] = (float) $totalItemProfit;

        $stats['net_profit'] =
            (float) $stats['total_item_profit']
            - (float) $stats['total_expenses']
            - (float) $stats['total_sale_returns'];
        // ── Sales last 7 days OR within date range chart ─────────────
        $chartDays  = 6;
        $chartStart = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subDays($chartDays)->startOfDay();
        $chartEnd   = $dateTo   ? \Carbon\Carbon::parse($dateTo)->endOfDay()     : now()->endOfDay();

        $salesChart = Sale::selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->when($paymentStatus, fn($q) => $q->where('payment_status', $paymentStatus))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartData   = [];
        $diff = $chartStart->diffInDays($chartEnd);
        $step = $diff > 30 ? (int) ceil($diff / 30) : 1;

        $cursor = $chartStart->copy();

        while ($cursor->lte($chartEnd)) {
            $date          = $cursor->toDateString();
            $chartLabels[] = $cursor->format('d M');
            $chartData[]   = (float) ($salesChart[$date] ?? 0);
            $cursor->addDays($step);
        }

        // ── Top 10 selling products ──────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('products.product_name, SUM(sale_items.qty) as total_qty, SUM(sale_items.line_total) as total_revenue')
            ->when($shopId, fn($q) => $q->where('sales.shop_id', $shopId))
            ->when($dateFrom, fn($q) => $q->whereDate('sales.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('sales.created_at', '<=', $dateTo))
            ->when($paymentStatus, fn($q) => $q->where('sales.payment_status', $paymentStatus))
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ── Top 7 customers ──────────────────────────────────────────
        if ($dateFrom || $dateTo || $paymentStatus) {
            $topCustomers = DB::table('sales')
                ->join('customers', 'customers.id', '=', 'sales.customer_id')
                ->selectRaw('customers.full_name, SUM(sales.grand_total) as total_sale, SUM(sales.paid) as total_paid, SUM(sales.due) as due')
                ->when($shopId, fn($q) => $q->where('sales.shop_id', $shopId))
                ->when($dateFrom,      fn($q) => $q->whereDate('sales.created_at', '>=', $dateFrom))
                ->when($dateTo,        fn($q) => $q->whereDate('sales.created_at', '<=', $dateTo))
                ->when($paymentStatus, fn($q) => $q->where('sales.payment_status', $paymentStatus))
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
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId), fn($q) => $q->whereNull('shop_id'))
            ->where('stock_qty', '<=', 10)
            ->orderBy('stock_qty')
            ->limit(6)
            ->get();

        // ── Recent 10 sales ──────────────────────────────────────────
        $recentSales = Sale::with(['customer', 'items'])
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->when($dateFrom,      fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,        fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($paymentStatus, fn($q) => $q->where('payment_status', $paymentStatus))
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
        ));
    }
}
