<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('received_cloths', function (Blueprint $table) {
            $table->id();
            $table->string('tailor_name');
            $table->foreignId('tailor_id')->nullable()->constrained('tailors')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('item_qty', 12, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('received_cloths');
    }
};
