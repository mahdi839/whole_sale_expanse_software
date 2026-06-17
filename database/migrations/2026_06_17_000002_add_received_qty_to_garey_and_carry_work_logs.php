<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('garey_man_work_logs', function (Blueprint $table) {
            $table->decimal('received_qty', 12, 2)->default(0)->after('qty');
        });

        Schema::table('carry_man_work_logs', function (Blueprint $table) {
            $table->decimal('received_qty', 12, 2)->default(0)->after('total_unit_kg');
        });
    }

    public function down(): void
    {
        Schema::table('garey_man_work_logs', function (Blueprint $table) {
            $table->dropColumn('received_qty');
        });

        Schema::table('carry_man_work_logs', function (Blueprint $table) {
            $table->dropColumn('received_qty');
        });
    }
};
