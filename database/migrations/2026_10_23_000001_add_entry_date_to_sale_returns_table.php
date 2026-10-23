<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->date('entry_date')->nullable()->after('customer_id');
        });

        $driver = Schema::getConnection()->getDriverName();
        $expression = $driver === 'sqlite' ? 'date(created_at)' : 'DATE(created_at)';

        DB::table('sale_returns')->whereNull('entry_date')->update([
            'entry_date' => DB::raw($expression),
        ]);
    }

    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropColumn('entry_date');
        });
    }
};
