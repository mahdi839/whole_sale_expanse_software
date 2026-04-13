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
 
            // ── Supplier (nullable so walk-in / manual entry still works) ──
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('seller_store_name')->nullable();          // kept for display / fallback
 
            // ── Product (nullable so free-text entry still works) ──
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name')->nullable();               // kept for display / fallback
            $table->string('product_code')->nullable();   // kept for display / fallback
 
            $table->string('purchased_by');
 
            // ── Quantities & pricing ──
            $table->decimal('qty',        12, 2)->default(0);
            $table->decimal('price',      12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->decimal('subtotal',   12, 2)->default(0);
            $table->decimal('grand_total',12, 2)->default(0);
 
            // ── Payment amounts (driven by payment_status) ──
            $table->decimal('due_amount',  12, 2)->default(0);  // shown when status = due | partial
            $table->decimal('paid_amount', 12, 2)->default(0);  // shown when status = paid | partial
 
            // ── Meta ──
            $table->string('cash_memo')->nullable();
            $table->date('date');
            $table->string('payment_method')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
 
            // ── Statuses ──
            $table->enum('purchase_status', ['received', 'partial', 'pending', 'ordered'])->default('pending');
            $table->enum('payment_status',  ['due', 'paid', 'partial'])->default('due');
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
