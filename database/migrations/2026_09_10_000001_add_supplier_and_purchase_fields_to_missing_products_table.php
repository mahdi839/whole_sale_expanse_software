<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missing_products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->decimal('purchase_rate', 12, 2)->default(0)->after('missing_qty');
            $table->decimal('purchase_value', 12, 2)->default(0)->after('purchase_rate');

            $table->index(['supplier_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('missing_products', function (Blueprint $table) {
            $table->dropIndex(['supplier_id', 'date']);
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn(['purchase_rate', 'purchase_value']);
        });
    }
};
