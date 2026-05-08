<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'bill_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('bill_no');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('sales', 'bill_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('bill_no')->nullable()->after('cash_memo');
            });
        }
    }
};
