<?php

use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;

it('preserves customer financial totals when editing profile details only', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);

    $user = User::factory()->create();
    $customer = Customer::create([
        'full_name' => 'Mehedi',
        'phone' => '01700000000',
        'total_sale' => 2000,
        'total_paid' => 0,
        'due' => 2000,
    ]);

    $this->actingAs($user)
        ->put(route('customers.update', $customer), [
            'full_name' => 'Mehedi',
            'phone' => '01700000000',
            'address' => 'Mirpur, Dhaka',
        ])
        ->assertRedirect(route('customers.index'));

    $customer->refresh();

    expect((float) $customer->total_sale)->toBe(2000.0)
        ->and((float) $customer->total_paid)->toBe(0.0)
        ->and((float) $customer->due)->toBe(2000.0)
        ->and($customer->address)->toBe('Mirpur, Dhaka');
});

it('builds a customer label from name and address', function () {
    $withAddress = new Customer(['full_name' => 'Rahim', 'address' => 'Mirpur, Dhaka']);
    $withoutAddress = new Customer(['full_name' => 'Karim', 'address' => null]);

    expect($withAddress->displayLabel())->toBe('Rahim - Mirpur, Dhaka')
        ->and($withoutAddress->displayLabel())->toBe('Karim');
});

it('shows customer name with address in sale and cash transaction dropdowns', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'is_admin' => true]);
    Customer::create([
        'shop_id' => $shop->id,
        'full_name' => 'Rahim',
        'phone' => '01711111111',
        'address' => 'Chistia Market',
    ]);

    $this->actingAs($user)->get(route('cash-transactions.create'))
        ->assertOk()
        ->assertSee('Rahim - Chistia Market');

    $this->actingAs($user)->get(route('sales.create'))
        ->assertOk()
        ->assertSee('Rahim - Chistia Market');

    $this->actingAs($user)->get(route('customers.index'))
        ->assertOk()
        ->assertSee('Rahim')
        ->assertSee('Chistia Market');
});

it('suggests customers after two characters and matches multiple terms', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $shop = Shop::create(['name' => 'Inaya Creation', 'code' => 'INAYA']);
    $user = User::factory()->create(['shop_id' => $shop->id, 'is_admin' => true]);
    Customer::create([
        'shop_id' => $shop->id,
        'full_name' => 'Mehedi Hasan',
        'phone' => '01700000000',
        'address' => 'Mirpur, Dhaka',
    ]);
    Customer::create([
        'shop_id' => $shop->id,
        'full_name' => 'Another Customer',
        'phone' => '01800000000',
    ]);

    $this->actingAs($user)
        ->getJson(route('customers.suggestions', ['q' => 'Me Hasan']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Mehedi Hasan')
        ->assertJsonPath('0.address', 'Mirpur, Dhaka')
        ->assertJsonPath('0.label', 'Mehedi Hasan - Mirpur, Dhaka');

    $this->actingAs($user)
        ->getJson(route('customers.suggestions', ['q' => 'M']))
        ->assertOk()
        ->assertExactJson([]);
});
