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
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')
                  ->constrained('sale_returns')->cascadeOnDelete();   // parent return
 
            $table->foreignId('sale_item_id')->nullable()
                  ->constrained('sale_items')->nullOnDelete();        // original sale line (nullable for standalone returns)
 
            $table->foreignId('product_id')
                  ->constrained('products')->restrictOnDelete();
 
            $table->decimal('qty', 12, 2);                           // qty being returned
            $table->decimal('price_on_sale', 12, 2);                 // price at time of original sale
            $table->decimal('line_total', 12, 2);  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
