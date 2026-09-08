<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missing_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('missing_qty', 12, 2);
            $table->date('date');
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missing_products');
    }
};
