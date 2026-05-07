<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_dues', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('party_type', ['customer', 'supplier']);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['party_type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_dues');
    }
};
