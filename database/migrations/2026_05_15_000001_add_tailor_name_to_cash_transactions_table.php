<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_transactions', 'tailor_name')) {
                $table->string('tailor_name')->nullable()->after('sales_man_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_transactions', 'tailor_name')) {
                $table->dropColumn('tailor_name');
            }
        });
    }
};
