<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code')->nullable()->unique()->after('sku');
            $table->decimal('selling_price', 12, 2)->default(0)->after('product_code');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('batch')->nullable()->after('bale_no');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('purchase_item_id')->nullable()->after('product_id')->constrained('purchase_items')->nullOnDelete();
            $table->string('batch')->nullable()->after('purchase_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_item_id');
            $table->dropColumn('batch');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('batch');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_code']);
            $table->dropColumn(['product_code', 'selling_price']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
