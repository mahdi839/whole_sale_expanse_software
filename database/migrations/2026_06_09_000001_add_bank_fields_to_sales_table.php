<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'bank')) {
                $table->string('bank')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('sales', 'bank_details')) {
                $table->string('bank_details')->nullable()->after('bank');
            }
        });

        DB::table('sales')
            ->where('payment_method', 'Bank')
            ->whereNull('bank_details')
            ->whereNotNull('cash_memo')
            ->update(['bank_details' => DB::raw('cash_memo')]);
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'bank_details')) {
                $table->dropColumn('bank_details');
            }

            if (Schema::hasColumn('sales', 'bank')) {
                $table->dropColumn('bank');
            }
        });
    }
};
