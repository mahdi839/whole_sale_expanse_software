<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shop;
use App\Models\User;
use Spatie\Permission\Middleware\PermissionMiddleware;

beforeEach(function () {
    $this->withoutMiddleware(PermissionMiddleware::class);
});

it('shows product sales counts returns and amounts on the report', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'is_admin' => true]);
    $sold = Product::create(['product_name' => 'Emerald Saree', 'sku' => 'ES-1', 'selling_price' => 500]);
    $unsold = Product::create(['product_name' => 'Hidden Kurti', 'sku' => 'HK-1', 'selling_price' => 200]);

    $sale = Sale::create([
        'reference' => 'SALE-RPT-1',
        'shop_id' => $shop->id,
        'grand_total' => 1500,
        'paid' => 1500,
        'payment_status' => 'paid',
        'status' => 'success',
    ]);
    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $sold->id,
        'qty' => 3,
        'price_on_sale' => 500,
        'line_total' => 1500,
    ]);

    $return = SaleReturn::create([
        'reference' => 'RET-RPT-1',
        'sale_id' => $sale->id,
        'subtotal' => 500,
        'return_amount' => 500,
        'return_status' => 'approved',
    ]);
    SaleReturnItem::create([
        'sale_return_id' => $return->id,
        'sale_item_id' => $sale->items()->first()->id,
        'product_id' => $sold->id,
        'qty' => 1,
        'price_on_sale' => 500,
        'line_total' => 500,
    ]);

    $this->actingAs($user)
        ->get(route('sales-reports.index', ['range' => 'all']))
        ->assertOk()
        ->assertSee('Product Sales Report')
        ->assertSee('Emerald Saree')
        ->assertDontSee('Hidden Kurti')
        ->assertSee('৳1,500.00')
        ->assertSee('৳500.00');
});

it('scopes the product sales report to the executive shop', function () {
    $shopA = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $shopB = Shop::create(['name' => 'Other Shop', 'code' => 'OTHER']);
    $user = User::factory()->create(['shop_id' => $shopA->id]);
    $productA = Product::create(['product_name' => 'Shop A Dress', 'sku' => 'A-1', 'selling_price' => 100]);
    $productB = Product::create(['product_name' => 'Shop B Dress', 'sku' => 'B-1', 'selling_price' => 100]);

    $saleA = Sale::create(['reference' => 'SALE-A', 'shop_id' => $shopA->id, 'grand_total' => 100, 'payment_status' => 'paid']);
    SaleItem::create(['sale_id' => $saleA->id, 'product_id' => $productA->id, 'qty' => 1, 'price_on_sale' => 100, 'line_total' => 100]);
    $saleB = Sale::create(['reference' => 'SALE-B', 'shop_id' => $shopB->id, 'grand_total' => 100, 'payment_status' => 'paid']);
    SaleItem::create(['sale_id' => $saleB->id, 'product_id' => $productB->id, 'qty' => 1, 'price_on_sale' => 100, 'line_total' => 100]);

    $this->actingAs($user)
        ->get(route('sales-reports.index', ['range' => 'all']))
        ->assertOk()
        ->assertSee('Shop A Dress')
        ->assertDontSee('Shop B Dress');
});

it('blocks the product sales report without the view sales reports permission', function () {
    $this->withMiddleware(PermissionMiddleware::class);

    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);

    $this->actingAs($user)
        ->get(route('sales-reports.index'))
        ->assertForbidden();
});

it('allows the product sales report with the view sales reports permission', function () {
    $this->withMiddleware(PermissionMiddleware::class);

    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->givePermissionTo('view sales reports');

    $this->actingAs($user)
        ->get(route('sales-reports.index', ['range' => 'all']))
        ->assertOk()
        ->assertSee('Product Sales Report');
});

it('downloads csv and pdf product sales reports', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'is_admin' => true]);
    $product = Product::create(['product_name' => 'Royal Lehenga', 'sku' => 'RL-1', 'selling_price' => 900]);
    $sale = Sale::create(['reference' => 'SALE-PDF', 'shop_id' => $shop->id, 'grand_total' => 1800, 'payment_status' => 'paid']);
    SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'qty' => 2, 'price_on_sale' => 900, 'line_total' => 1800]);

    $csv = $this->actingAs($user)
        ->get(route('sales-reports.export.csv', ['range' => 'all']));

    $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($csv->streamedContent())
        ->toContain('Royal Lehenga')
        ->toContain('1800.00');

    $this->actingAs($user)
        ->get(route('sales-reports.export.pdf', ['range' => 'all']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
