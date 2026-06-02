<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('documents')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('salary_month');
            $table->decimal('amount', 12, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'salary_month']);
            $table->index('salary_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
        Schema::dropIfExists('employees');
    }
};
