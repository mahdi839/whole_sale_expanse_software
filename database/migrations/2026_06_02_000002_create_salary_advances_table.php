<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('advance_month');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['employee_id', 'advance_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};
