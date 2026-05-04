<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('is_admin')->constrained('shops')->nullOnDelete();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('stock_qty', 12, 2)->default(0)->change();
            $table->foreignId('shop_id')->nullable()->after('product_id')->constrained('shops')->cascadeOnDelete();
            $table->index(['shop_id', 'product_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('reference')->constrained('shops')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('shop_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'product_id']);
            $table->dropConstrainedForeignId('shop_id');
            $table->integer('stock_qty')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::dropIfExists('shops');
    }
};
