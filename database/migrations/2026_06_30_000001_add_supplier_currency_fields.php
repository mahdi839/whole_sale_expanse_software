<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('currency', 3)->default('BDT')->after('address');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->decimal('supplier_amount', 12, 2)->nullable()->after('amount');
            $table->string('supplier_currency', 3)->nullable()->after('supplier_amount');
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn(['supplier_amount', 'supplier_currency']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
