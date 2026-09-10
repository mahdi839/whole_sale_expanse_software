<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missing_products', function (Blueprint $table) {
            $table->string('bill_no')->nullable()->after('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('missing_products', function (Blueprint $table) {
            $table->dropColumn('bill_no');
        });
    }
};
