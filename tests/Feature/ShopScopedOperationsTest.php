<?php

use App\Models\CarryMan;
use App\Models\Cheque;
use App\Models\ComputerMan;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\GareyMan;
use App\Models\ManualDue;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Tailor;
use App\Models\User;
use Spatie\Permission\Middleware\PermissionMiddleware;

beforeEach(function () {
    $this->withoutMiddleware(PermissionMiddleware::class);
});

it('scopes sales expenses and customer and sale dues to the executive shop', function () {
    $shopA = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $shopB = Shop::create(['name' => 'Other Shop', 'code' => 'OTHER']);
    $user = User::factory()->create(['shop_id' => $shopA->id]);
    $customerA = Customer::create(['shop_id' => $shopA->id, 'full_name' => 'Rahim Customer', 'total_sale' => 500, 'due' => 500]);
    $customerB = Customer::create(['shop_id' => $shopB->id, 'full_name' => 'Hidden Customer', 'total_sale' => 900, 'due' => 900]);

    Expense::create(['reference' => 'EXP-A', 'shop_id' => $shopA->id, 'category' => 'Office', 'amount' => 123, 'date' => now()]);
    Expense::create(['reference' => 'EXP-B', 'shop_id' => $shopB->id, 'category' => 'Office', 'amount' => 987, 'date' => now()]);
    Sale::create(['reference' => 'SALE-A', 'shop_id' => $shopA->id, 'customer_id' => $customerA->id, 'grand_total' => 500, 'due' => 500, 'payment_status' => 'due']);
    Sale::create(['reference' => 'SALE-B', 'shop_id' => $shopB->id, 'customer_id' => $customerB->id, 'grand_total' => 900, 'due' => 900, 'payment_status' => 'due']);

    $this->actingAs($user)->get(route('sales.index'))
        ->assertOk()
        ->assertSee('৳123.00')
        ->assertDontSee('৳987.00');

    $this->actingAs($user)->get(route('dues.customer'))
        ->assertOk()
        ->assertSee('Rahim Customer')
        ->assertDontSee('Hidden Customer');

    $this->actingAs($user)->get(route('dues.sale'))
        ->assertOk()
        ->assertSee('SALE-A')
        ->assertDontSee('SALE-B');
});

it('scopes customer manual dues but keeps supplier manual dues global', function () {
    $shopA = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $shopB = Shop::create(['name' => 'Other Shop', 'code' => 'OTHER']);
    $user = User::factory()->create(['shop_id' => $shopA->id]);
    $customerA = Customer::create(['shop_id' => $shopA->id, 'full_name' => 'Visible Customer']);
    $customerB = Customer::create(['shop_id' => $shopB->id, 'full_name' => 'Hidden Customer']);
    $supplier = Supplier::create(['name' => 'Global Supplier']);

    ManualDue::create(['party_type' => 'customer', 'adjustment_type' => 'add', 'customer_id' => $customerA->id, 'amount' => 10, 'date' => now()]);
    ManualDue::create(['party_type' => 'customer', 'adjustment_type' => 'add', 'customer_id' => $customerB->id, 'amount' => 20, 'date' => now()]);
    ManualDue::create(['party_type' => 'supplier', 'adjustment_type' => 'add', 'supplier_id' => $supplier->id, 'amount' => 30, 'date' => now()]);

    $this->actingAs($user)->get(route('dues.manual'))
        ->assertOk()
        ->assertSee('Visible Customer')
        ->assertDontSee('Hidden Customer')
        ->assertSee('Global Supplier');
});

it('transfers stock from a shop back to central stock', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $product = Product::create(['product_name' => 'Dress', 'sku' => 'D-1', 'selling_price' => 100]);
    Stock::create(['product_id' => $product->id, 'shop_id' => $shop->id, 'stock_qty' => 10]);

    $this->actingAs($user)->post(route('stocks.transfers.store'), [
        'from_shop_id' => $shop->id,
        'destination_type' => 'central',
        'product_id' => $product->id,
        'qty' => 4,
    ])->assertRedirect(route('stocks.adjustments'));

    expect((float) Stock::where('product_id', $product->id)->where('shop_id', $shop->id)->value('stock_qty'))->toBe(6.0)
        ->and((float) Stock::where('product_id', $product->id)->whereNull('shop_id')->value('stock_qty'))->toBe(4.0);
});

it('assigns an executives shop to a cheque and rejects another shops customer', function () {
    $shopA = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $shopB = Shop::create(['name' => 'Other Shop', 'code' => 'OTHER']);
    $user = User::factory()->create(['shop_id' => $shopA->id]);
    $customerA = Customer::create(['shop_id' => $shopA->id, 'full_name' => 'Own Customer']);
    $customerB = Customer::create(['shop_id' => $shopB->id, 'full_name' => 'Other Customer']);

    $this->actingAs($user)->get(route('cheques.create'))
        ->assertOk()
        ->assertSee('Inaya Creation')
        ->assertSee('Own Customer')
        ->assertDontSee('Other Customer');

    $this->actingAs($user)->post(route('cheques.store'), [
        'cheque_no' => 'CHQ-1', 'shop_id' => $shopB->id, 'customer_id' => $customerA->id,
        'bank' => 'Bank', 'amount' => 100, 'issue_date' => now()->toDateString(), 'status' => 'pending',
    ])->assertRedirect(route('cheques.index'));

    expect(Cheque::sole()->shop_id)->toBe($shopA->id);

    $this->actingAs($user)->post(route('cheques.store'), [
        'cheque_no' => 'CHQ-2', 'customer_id' => $customerB->id,
        'bank' => 'Bank', 'amount' => 100, 'issue_date' => now()->toDateString(), 'status' => 'pending',
    ])->assertStatus(422);
});

it('lets a super admin select a cheque shop', function () {
    $shopA = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $shopB = Shop::create(['name' => 'Second Shop', 'code' => 'SECOND']);
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = Customer::create(['shop_id' => $shopB->id, 'full_name' => 'Second Shop Customer']);

    $this->actingAs($admin)->get(route('cheques.create'))
        ->assertOk()
        ->assertSee('Inaya Creation')
        ->assertSee('Second Shop');

    $this->actingAs($admin)->post(route('cheques.store'), [
        'cheque_no' => 'ADMIN-CHQ', 'shop_id' => $shopB->id, 'customer_id' => $customer->id,
        'bank' => 'Bank', 'amount' => 200, 'issue_date' => now()->toDateString(), 'status' => 'pending',
    ])->assertRedirect(route('cheques.index'));

    expect(Cheque::sole()->shop_id)->toBe($shopB->id);
});

it('downloads the requested inventory pdf reports', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $product = Product::create(['product_name' => 'Dress', 'sku' => 'D-1', 'selling_price' => 100]);
    Stock::create(['product_id' => $product->id, 'shop_id' => $shop->id, 'stock_qty' => 2]);

    $this->actingAs($user)->get(route('products.export.pdf'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
    $this->actingAs($user)->get(route('shops.export.pdf', $shop))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('downloads customer and supplier transaction pdf reports with the extended data', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $customer = Customer::create(['shop_id' => $shop->id, 'full_name' => 'Customer', 'total_sale' => 100, 'total_paid' => 40, 'due' => 60]);
    $supplier = Supplier::create(['name' => 'Supplier', 'total_purchase' => 100, 'due' => 100]);
    $product = Product::create(['product_name' => 'Dress', 'sku' => 'D-1', 'selling_price' => 100]);
    $purchase = Purchase::create([
        'reference' => 'PUR-PDF', 'supplier_id' => $supplier->id, 'seller_store_name' => 'Supplier Store',
        'bill_no' => 'BILL-10', 'purchased_by' => 'Admin', 'grand_total' => 100, 'due_amount' => 100, 'date' => now(),
    ]);
    PurchaseItem::create(['purchase_id' => $purchase->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 100, 'line_total' => 100]);

    $this->actingAs($user)->get(route('customers.transactions.export', [$customer, 'format' => 'pdf']))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
    $this->actingAs($user)->get(route('suppliers.transactions.export', [$supplier, 'format' => 'pdf']))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('shows the shop address and proprietor number on sale invoices', function () {
    $shop = Shop::create([
        'name' => 'Inaya Creation',
        'code' => 'INAYA',
        'address' => 'House 10, Dhaka',
        'phone' => '01700000000',
        'proprietor_number' => 'PROP-123',
    ]);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $product = Product::create(['product_name' => 'Dress', 'sku' => 'D-1', 'selling_price' => 100]);
    $sale = Sale::create([
        'reference' => 'SALE-INVOICE', 'shop_id' => $shop->id, 'grand_total' => 100,
        'paid' => 100, 'payment_status' => 'paid',
    ]);
    SaleItem::create([
        'sale_id' => $sale->id, 'product_id' => $product->id, 'qty' => 1,
        'price_on_sale' => 100, 'line_total' => 100,
    ]);

    $this->actingAs($user)->get(route('sales.invoice', $sale))
        ->assertOk()
        ->assertSee('House 10, Dhaka')
        ->assertSee('Proprietor Number: PROP-123');
});

it('keeps invoice due snapshots correct after later customer activity', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $customer = Customer::create(['shop_id' => $shop->id, 'full_name' => 'Snapshot Customer']);
    $product = Product::create(['product_name' => 'Dress', 'sku' => 'D-1', 'selling_price' => 50]);
    Stock::create(['product_id' => $product->id, 'shop_id' => $shop->id, 'stock_qty' => 5]);

    $this->actingAs($user)->post(route('dues.store'), [
        'party_type' => 'customer',
        'adjustment_type' => 'add',
        'customer_id' => $customer->id,
        'amount' => 100,
        'date' => now()->toDateString(),
    ])->assertRedirect(route('dues.manual'));

    $this->actingAs($user)->post(route('cash-transactions.store'), [
        'direction' => 'in',
        'type' => 'collection',
        'amount' => 40,
        'date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'cash_entry_type' => 'customer',
    ])->assertRedirect(route('cash-transactions.index'));

    $this->actingAs($user)->post(route('dues.store'), [
        'party_type' => 'customer',
        'adjustment_type' => 'add',
        'customer_id' => $customer->id,
        'amount' => 30,
        'date' => now()->toDateString(),
    ])->assertRedirect(route('dues.manual'));

    $this->actingAs($user)->post(route('sales.store'), [
        'reference' => 'SALE-SNAPSHOT',
        'customer_id' => $customer->id,
        'payment_status' => 'due',
        'items' => [[
            'product_id' => $product->id,
            'qty' => 1,
            'price_on_sale' => 50,
        ]],
    ])->assertRedirect(route('sales.index'));

    $sale = Sale::where('reference', 'SALE-SNAPSHOT')->firstOrFail();

    expect((float) $sale->customer_balance_before_sale)->toBe(90.0)
        ->and((float) $sale->customer_due_after_sale)->toBe(140.0)
        ->and((float) $customer->fresh()->due)->toBe(140.0);

    $this->actingAs($user)->post(route('cash-transactions.store'), [
        'direction' => 'in',
        'type' => 'collection',
        'amount' => 25,
        'date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'cash_entry_type' => 'customer',
    ])->assertRedirect(route('cash-transactions.index'));

    $this->actingAs($user)->post(route('dues.store'), [
        'party_type' => 'customer',
        'adjustment_type' => 'add',
        'customer_id' => $customer->id,
        'amount' => 10,
        'date' => now()->toDateString(),
    ])->assertRedirect(route('dues.manual'));

    expect((float) $customer->fresh()->due)->toBe(125.0);

    $this->actingAs($user)->get(route('sales.invoice', $sale))
        ->assertOk()
        ->assertSeeInOrder([
            'Previous Due',
            '90',
            'Customer Total Due',
            '140',
        ]);

    $this->actingAs($user)->put(route('sales.update', $sale), [
        'reference' => 'SALE-SNAPSHOT',
        'customer_id' => $customer->id,
        'payment_status' => 'due',
        'items' => [[
            'product_id' => $product->id,
            'qty' => 1,
            'price_on_sale' => 70,
        ]],
    ])->assertRedirect(route('sales.index'));

    $sale->refresh();

    expect((float) $sale->customer_balance_before_sale)->toBe(90.0)
        ->and((float) $sale->customer_due_after_sale)->toBe(160.0)
        ->and((float) $customer->fresh()->due)->toBe(145.0);

    $this->actingAs($user)->get(route('sales.invoice', $sale))
        ->assertOk()
        ->assertSeeInOrder([
            'Previous Due',
            '90',
            'Customer Total Due',
            '160',
        ]);
});

it('downloads tailor and worker profile and work log pdf reports', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $tailor = Tailor::create(['name' => 'Tailor One']);
    $carryMan = CarryMan::create(['name' => 'Carry One']);
    $computerMan = ComputerMan::create(['name' => 'Computer One']);
    $gareyMan = GareyMan::create(['name' => 'Garey One']);

    foreach ([
        route('tailors.export.pdf', $tailor),
        route('carry-men.export.pdf', $carryMan),
        route('computer-men.export.pdf', $computerMan),
        route('garey-men.export.pdf', $gareyMan),
        route('cloth-sewings.export.pdf'),
        route('carry-man-work-logs.export.pdf'),
        route('computer-man-work-logs.export.pdf'),
        route('garey-man-work-logs.export.pdf'),
    ] as $url) {
        $this->actingAs($user)->get($url)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
});
