<?php

use App\Models\Customer;
use App\Models\User;

it('preserves customer financial totals when editing profile details only', function () {
    $this->withoutMiddleware();

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
