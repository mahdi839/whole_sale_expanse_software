<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->string('seller_store_name')->nullable();
            $table->string('purchased_by');

            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);

            $table->string('cash_memo')->nullable();
            $table->date('date');
            $table->string('payment_method')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();

            $table->string('purchase_status')->default('pending'); // received, partial, pending, ordered
            $table->string('payment_status')->default('due');      // due, paid, partial

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};