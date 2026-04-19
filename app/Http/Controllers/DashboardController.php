<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Summary cards ────────────────────────────────────────────
        $stats = [
            'total_products'        => Product::count(),
            'total_customers'       => Customer::count(),
            'total_sales'           => Sale::sum('grand_total'),
            'total_sales_due'       => Sale::sum('due'),
            'total_sale_returns'    => SaleReturn::where('return_status', 'approved')->sum('return_amount'),
            'total_purchases'       => Purchase::sum('grand_total'),
            'total_purchase_returns'=> PurchaseReturn::where('return_status', 'approved')->sum('return_amount'),
            'total_purchase_due'    => Purchase::sum('due_amount'),
        ];

        // ── Sales last 7 days (chart) ────────────────────────────────
        $salesChart = Sale::selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date           = now()->subDays($i)->toDateString();
            $chartLabels[]  = now()->subDays($i)->format('D');
            $chartData[]    = (float) ($salesChart[$date] ?? 0);
        }

        // ── Top 10 selling products ──────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('products.product_name, SUM(sale_items.qty) as total_qty, SUM(sale_items.line_total) as total_revenue')
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ── Top 7 customers ──────────────────────────────────────────
        $topCustomers = Customer::orderByDesc('total_sale')
            ->limit(7)
            ->get(['full_name', 'total_sale', 'total_paid', 'due']);

        // ── Low stock alerts (qty <= 10) ─────────────────────────────
        $lowStock = Stock::with('product')
            ->where('stock_qty', '<=', 10)
            ->orderBy('stock_qty')
            ->limit(6)
            ->get();

        // ── Recent 10 sales ──────────────────────────────────────────
        $recentSales = Sale::with(['customer', 'items'])
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'stats',
            'chartLabels',
            'chartData',
            'topProducts',
            'topCustomers',
            'lowStock',
            'recentSales',
        ));
    }
}