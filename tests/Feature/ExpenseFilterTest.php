<?php

use App\Models\Expense;
use App\Models\User;

it('shows only todays expenses by default', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $user = User::factory()->create();

    Expense::create([
        'reference' => 'EXP-TODAY',
        'category' => 'Office',
        'amount' => 100,
        'date' => now()->toDateString(),
    ]);
    Expense::create([
        'reference' => 'EXP-YESTERDAY',
        'category' => 'Office',
        'amount' => 200,
        'date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertSee('EXP-TODAY')
        ->assertDontSee('EXP-YESTERDAY')
        ->assertSee('name="date_from"', false)
        ->assertSee('value="'.now()->toDateString().'"', false);
});
