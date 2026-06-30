<?php

use App\Models\User;

it('requires a payment method when creating a sale', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('sales.create'))
        ->post(route('sales.store'), [])
        ->assertRedirect(route('sales.create'))
        ->assertSessionHasErrors('payment_method');
});
