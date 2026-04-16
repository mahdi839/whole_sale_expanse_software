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
               $table->string('reference')->unique();                    // RET-000001
            $table->foreignId('sale_id')->nullable()
                  ->constrained('sales')->nullOnDelete();             // original sale (nullable if standalone)
            $table->foreignId('customer_id')->nullable()
                  ->constrained('customers')->nullOnDelete();
 
            $table->decimal('discount', 12, 2)->default(0);          // overall return-level discount
            $table->decimal('subtotal', 12, 2)->default(0);          // sum of line totals before discount
            $table->decimal('return_amount', 12, 2)->default(0);     // subtotal - discount (amount to refund)

            $table->string('return_type')->default('refund');         // refund | exchange | credit
            $table->string('return_status')->default('pending');      // pending | approved | rejected
 
            $table->string('payment_method')->nullable();             // how refund is issued
            $table->string('cash_memo')->nullable();                 // supporting file path
            $table->text('note')->nullable();
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
