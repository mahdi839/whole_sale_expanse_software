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
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
              $table->string('reference')->unique();               // RET-000001
            $table->foreignId('sale_id')->nullable()
                  ->constrained('sales')->nullOnDelete();        // original sale
            $table->foreignId('customer_id')->nullable()
                  ->constrained('customers')->nullOnDelete();
            $table->foreignId('product_id')->nullable()
                  ->constrained('products')->nullOnDelete();
            $table->string('product_name')->nullable();
            $table->string('product_code')->nullable();
            $table->decimal('qty', 12, 2);                      // qty being returned
            $table->decimal('price_on_sale', 12, 2);            // original sale price
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('return_amount', 12, 2);            // amount to refund
            $table->string('return_type')->default('refund');   // refund | exchange | credit
            $table->string('return_status')->default('pending');// pending | approved | rejected
            $table->string('payment_method')->nullable();
            $table->string('cash_memo')->nullable();
            $table->string('document')->nullable();
            $table->text('reason')->nullable();
            $table->text('note')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};
