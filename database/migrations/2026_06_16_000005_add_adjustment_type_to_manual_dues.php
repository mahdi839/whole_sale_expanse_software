<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_dues', function (Blueprint $table) {
            if (! Schema::hasColumn('manual_dues', 'adjustment_type')) {
                $table->string('adjustment_type')->default('add')->after('party_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('manual_dues', function (Blueprint $table) {
            if (Schema::hasColumn('manual_dues', 'adjustment_type')) {
                $table->dropColumn('adjustment_type');
            }
        });
    }
};
