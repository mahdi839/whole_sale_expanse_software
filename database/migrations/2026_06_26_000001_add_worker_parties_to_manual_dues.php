<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE manual_dues MODIFY party_type VARCHAR(50) NOT NULL");
        }

        Schema::table('manual_dues', function (Blueprint $table) {
            if (! Schema::hasColumn('manual_dues', 'tailor_id')) {
                $table->foreignId('tailor_id')->nullable()->after('supplier_id')->constrained('tailors')->nullOnDelete();
            }

            if (! Schema::hasColumn('manual_dues', 'carry_man_id')) {
                $table->foreignId('carry_man_id')->nullable()->after('tailor_id')->constrained('carry_men')->nullOnDelete();
            }

            if (! Schema::hasColumn('manual_dues', 'computer_man_id')) {
                $table->foreignId('computer_man_id')->nullable()->after('carry_man_id')->constrained('computer_men')->nullOnDelete();
            }

            if (! Schema::hasColumn('manual_dues', 'garey_man_id')) {
                $table->foreignId('garey_man_id')->nullable()->after('computer_man_id')->constrained('garey_men')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('manual_dues', function (Blueprint $table) {
            foreach (['garey_man_id', 'computer_man_id', 'carry_man_id', 'tailor_id'] as $column) {
                if (Schema::hasColumn('manual_dues', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
