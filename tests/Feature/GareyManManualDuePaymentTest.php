<?php

use App\Models\GareyMan;
use App\Models\Shop;
use App\Models\User;

it('keeps the unpaid manual due after a partial garey man cash payment', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $shop = Shop::create(['name' => 'Main Shop', 'code' => 'MAIN']);
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $gareyMan = GareyMan::create(['name' => 'Test Garey Man']);

    $this->actingAs($user)
        ->post(route('dues.store'), [
            'party_type' => 'garey_man',
            'adjustment_type' => 'add',
            'garey_man_id' => $gareyMan->id,
            'amount' => 500,
            'date' => now()->toDateString(),
        ])
        ->assertRedirect(route('dues.manual'));

    $gareyMan->refresh();
    expect((float) $gareyMan->total_due)->toBe(500.0);

    $this->actingAs($user)
        ->post(route('cash-transactions.store'), [
            'type' => 'manual_out',
            'direction' => 'out',
            'cash_entry_type' => 'garey_man',
            'garey_man_id' => $gareyMan->id,
            'amount' => 300,
            'date' => now()->toDateString(),
        ])
        ->assertRedirect(route('cash-transactions.index'));

    $gareyMan->refresh();

    expect((float) $gareyMan->total_paid)->toBe(300.0)
        ->and((float) $gareyMan->total_due)->toBe(200.0)
        ->and((float) $gareyMan->advance)->toBe(0.0);
});
