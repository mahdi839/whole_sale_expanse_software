<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_no')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('bank');
            $table->decimal('amount', 12, 2);
            $table->date('issue_date');
            $table->date('deposit_date')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('documents')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
