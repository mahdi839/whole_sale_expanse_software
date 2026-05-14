<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloth_sewings', function (Blueprint $table) {
            $table->id();
            $table->string('tailor_name');
            $table->string('product_name');
            $table->string('design_code');
            $table->decimal('item_qty', 12, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloth_sewings');
    }
};
