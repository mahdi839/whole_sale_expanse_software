<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_distributions', function (Blueprint $table) {
            $table->string('carry_man')->nullable()->after('distributor');
        });
    }

    public function down(): void
    {
        Schema::table('stock_distributions', function (Blueprint $table) {
            $table->dropColumn('carry_man');
        });
    }
};
