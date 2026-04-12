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
            $table->string('seller_store_name');
            $table->string('purchased_by');
            $table->string('product_name');
            $table->string('product_code')->nullable();

            $table->decimal('qty', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('cash_memo')->nullable();
            $table->date('date');

            $table->string('payment_method')->nullable();
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->string('document')->nullable();

            $table->enum('purchase_status', ['received', 'partial', 'pending', 'ordered'])->default('pending');
            $table->enum('payment_status', ['due', 'paid', 'partial'])->default('due');

            $table->text('note')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
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
