<?php

use App\Models\CashTransaction;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;

it('stores a supplier currency and defaults older quick-add requests to BDT', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('suppliers.store'), [
            'name' => 'Delhi Textiles',
            'currency' => 'INR',
        ])
        ->assertRedirect(route('suppliers.index'));

    expect(Supplier::where('name', 'Delhi Textiles')->value('currency'))->toBe('INR');

    $this->actingAs($user)
        ->postJson(route('suppliers.store'), ['name' => 'Local Fabrics'])
        ->assertCreated()
        ->assertJsonPath('currency', 'BDT');
});

it('uses supplier currency amount for due and BDT amount for cash', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $user = User::factory()->create();
    $supplier = Supplier::create([
        'name' => 'US Fabric House',
        'currency' => 'USD',
        'total_purchase' => 1000,
        'total_paid' => 0,
        'due' => 1000,
    ]);

    $this->actingAs($user)
        ->post(route('cash-transactions.store'), [
            'type' => 'manual_out',
            'direction' => 'out',
            'cash_entry_type' => 'supplier',
            'supplier_id' => $supplier->id,
            'amount' => 12200,
            'supplier_amount' => 100,
            'date' => now()->toDateString(),
        ])
        ->assertRedirect(route('cash-transactions.index'));

    $transaction = CashTransaction::sole();
    $supplier->refresh();

    expect((float) $transaction->amount)->toBe(12200.0)
        ->and((float) $transaction->supplier_amount)->toBe(100.0)
        ->and($transaction->supplier_currency)->toBe('USD')
        ->and((float) $supplier->total_paid)->toBe(100.0)
        ->and((float) $supplier->due)->toBe(900.0);

    $this->actingAs($user)
        ->delete(route('cash-transactions.destroy', $transaction))
        ->assertRedirect(route('cash-transactions.index'));

    $supplier->refresh();

    expect((float) $supplier->total_paid)->toBe(0.0)
        ->and((float) $supplier->due)->toBe(1000.0);
});

it('requires supplier amount for a supplier cash out', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $user = User::factory()->create();
    $supplier = Supplier::create([
        'name' => 'Delhi Textiles',
        'currency' => 'INR',
    ]);

    $this->actingAs($user)
        ->from(route('cash-transactions.create'))
        ->post(route('cash-transactions.store'), [
            'type' => 'manual_out',
            'direction' => 'out',
            'cash_entry_type' => 'supplier',
            'supplier_id' => $supplier->id,
            'amount' => 5000,
            'date' => now()->toDateString(),
        ])
        ->assertRedirect(route('cash-transactions.create'))
        ->assertSessionHasErrors('supplier_amount');
});

it('shows supplier currency on supplier and purchase index pages', function () {
    $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
    $user = User::factory()->create();
    $supplier = Supplier::create([
        'name' => 'US Fabric House',
        'currency' => 'USD',
        'total_purchase' => 250,
        'total_paid' => 100,
        'due' => 150,
    ]);
    Purchase::create([
        'reference' => 'PUR-000001',
        'supplier_id' => $supplier->id,
        'purchased_by' => 'Admin',
        'grand_total' => 250,
        'paid_amount' => 100,
        'due_amount' => 150,
        'date' => now()->toDateString(),
        'purchase_status' => 'received',
        'payment_status' => 'partial',
    ]);

    $this->actingAs($user)
        ->get(route('suppliers.index'))
        ->assertOk()
        ->assertSee('USD 100.00')
        ->assertSee('USD 150.00');

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertOk()
        ->assertSee('USD 250.00')
        ->assertSee('USD 100.00')
        ->assertSee('USD 150.00');
});
