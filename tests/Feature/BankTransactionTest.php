<?php

use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\User;

it('creates a manual bank transaction with customer entry type', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'is_admin' => true]);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'full_name' => 'Rahim',
        'address' => 'Chistia Market',
        'total_sale' => 500,
        'total_paid' => 0,
        'due' => 500,
    ]);

    $this->actingAs($user)->post(route('bank-transactions.store'), [
        'shop_id' => $shop->id,
        'direction' => 'in',
        'bank_name' => 'Dutch Bangla Bank',
        'bank_details' => 'TXN-99',
        'amount' => 150,
        'date' => now()->toDateString(),
        'entry_type' => 'customer',
        'customer_id' => $customer->id,
        'note' => 'Bank collection',
    ])->assertRedirect(route('bank-transactions.index'));

    $entry = BankTransaction::first();

    expect($entry)->not->toBeNull()
        ->and($entry->bank_name)->toBe('Dutch Bangla Bank')
        ->and($entry->entry_type)->toBe('customer')
        ->and($entry->customer_id)->toBe($customer->id)
        ->and((float) $customer->fresh()->total_paid)->toBe(150.0);

    $this->actingAs($user)->get(route('bank-transactions.index'))
        ->assertOk()
        ->assertSee('Dutch Bangla Bank')
        ->assertSee('Rahim');
});

it('auto creates a bank entry when a sale is paid by bank', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'is_admin' => true]);
    $customer = Customer::create(['shop_id' => $shop->id, 'full_name' => 'Karim', 'address' => 'Mirpur']);
    $product = Product::create(['product_name' => 'Dress', 'sku' => 'D-BANK', 'selling_price' => 200]);
    Stock::create(['product_id' => $product->id, 'shop_id' => $shop->id, 'stock_qty' => 5]);

    $this->actingAs($user)->post(route('sales.store'), [
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'payment_status' => 'paid',
        'payment_method' => 'Bank',
        'bank' => 'City Bank',
        'bank_details' => 'A/C 12345',
        'paid' => 200,
        'items' => [[
            'product_id' => $product->id,
            'qty' => 1,
            'price_on_sale' => 200,
        ]],
    ])->assertRedirect(route('sales.index'));

    $sale = Sale::first();
    $entry = BankTransaction::where('source_type', 'sale')->where('source_id', $sale->id)->first();

    expect($entry)->not->toBeNull()
        ->and($entry->bank_name)->toBe('City Bank')
        ->and($entry->bank_details)->toBe('A/C 12345')
        ->and($entry->entry_type)->toBe('customer')
        ->and($entry->customer_id)->toBe($customer->id)
        ->and((float) $entry->amount)->toBe(200.0)
        ->and($entry->direction)->toBe('in');
});
