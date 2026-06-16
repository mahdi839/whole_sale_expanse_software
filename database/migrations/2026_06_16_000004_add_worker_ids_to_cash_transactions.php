<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_transactions', 'carry_man_id')) {
                $table->foreignId('carry_man_id')->nullable()->after('tailor_id')->constrained('carry_men')->nullOnDelete();
            }

            if (! Schema::hasColumn('cash_transactions', 'computer_man_id')) {
                $table->foreignId('computer_man_id')->nullable()->after('carry_man_id')->constrained('computer_men')->nullOnDelete();
            }

            if (! Schema::hasColumn('cash_transactions', 'garey_man_id')) {
                $table->foreignId('garey_man_id')->nullable()->after('computer_man_id')->constrained('garey_men')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_transactions', 'garey_man_id')) {
                $table->dropConstrainedForeignId('garey_man_id');
            }

            if (Schema::hasColumn('cash_transactions', 'computer_man_id')) {
                $table->dropConstrainedForeignId('computer_man_id');
            }

            if (Schema::hasColumn('cash_transactions', 'carry_man_id')) {
                $table->dropConstrainedForeignId('carry_man_id');
            }
        });
    }
};
