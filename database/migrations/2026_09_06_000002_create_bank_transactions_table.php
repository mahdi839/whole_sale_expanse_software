<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('direction', ['in', 'out'])->default('in');
            $table->string('type')->default('manual');
            $table->string('entry_type')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_details')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('document')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
            $table->index(['date', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
