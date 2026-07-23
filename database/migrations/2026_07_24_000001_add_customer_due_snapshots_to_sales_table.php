<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('customer_balance_before_sale', 12, 2)->nullable()->after('due');
            $table->decimal('customer_due_after_sale', 12, 2)->nullable()->after('customer_balance_before_sale');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'customer_balance_before_sale',
                'customer_due_after_sale',
            ]);
        });
    }
};
