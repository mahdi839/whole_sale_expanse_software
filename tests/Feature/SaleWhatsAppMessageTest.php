<?php

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Spatie\Permission\Middleware\PermissionMiddleware;

beforeEach(function () {
    $this->withoutMiddleware(PermissionMiddleware::class);
});

it('includes the customer total due in sale whatsapp messages', function () {
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'full_name' => 'Rahim Customer',
        'phone' => '01700000000',
        'total_sale' => 1000,
        'total_paid' => 250,
        'due' => 750,
    ]);

    Sale::create([
        'reference' => 'SALE-WHATSAPP',
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'grand_total' => 500,
        'paid' => 200,
        'due' => 300,
        'payment_status' => 'partial',
        'status' => 'success',
    ]);

    $response = $this->actingAs($user)->get(route('sales.index'));

    $response->assertOk();

    $encodedMessage = urlencode(
        'Hello Rahim Customer, your invoice SALE-WHATSAPP'.
        '. Total: ৳500.00'.
        ', Paid: ৳200.00'.
        ', Due: ৳300.00'.
        ', Your Total Due: ৳750.00'
    );

    expect(substr_count($response->getContent(), $encodedMessage))->toBe(2);
});
