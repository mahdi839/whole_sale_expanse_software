<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carry_man_work_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('carry_man_work_logs', 'memo_no')) {
                $table->string('memo_no')->nullable()->after('date');
            }
        });

        Schema::table('computer_man_work_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('computer_man_work_logs', 'memo_no')) {
                $table->string('memo_no')->nullable()->after('date');
            }
        });

        Schema::table('garey_man_work_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('garey_man_work_logs', 'memo_no')) {
                $table->string('memo_no')->nullable()->after('date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('garey_man_work_logs', function (Blueprint $table) {
            if (Schema::hasColumn('garey_man_work_logs', 'memo_no')) {
                $table->dropColumn('memo_no');
            }
        });

        Schema::table('computer_man_work_logs', function (Blueprint $table) {
            if (Schema::hasColumn('computer_man_work_logs', 'memo_no')) {
                $table->dropColumn('memo_no');
            }
        });

        Schema::table('carry_man_work_logs', function (Blueprint $table) {
            if (Schema::hasColumn('carry_man_work_logs', 'memo_no')) {
                $table->dropColumn('memo_no');
            }
        });
    }
};
