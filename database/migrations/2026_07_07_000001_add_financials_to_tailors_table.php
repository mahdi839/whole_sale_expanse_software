<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tailors', function (Blueprint $table) {
            if (! Schema::hasColumn('tailors', 'total_paid')) {
                $table->decimal('total_paid', 12, 2)->default(0)->after('document_path');
            }

            if (! Schema::hasColumn('tailors', 'total_due')) {
                $table->decimal('total_due', 12, 2)->default(0)->after('total_paid');
            }

            if (! Schema::hasColumn('tailors', 'advance')) {
                $table->decimal('advance', 12, 2)->default(0)->after('total_due');
            }
        });

        DB::table('tailors')
            ->select('id')
            ->orderBy('id')
            ->each(function ($tailor) {
                $manualDue = (float) DB::table('manual_dues')
                    ->where('tailor_id', $tailor->id)
                    ->selectRaw('COALESCE(SUM(CASE WHEN adjustment_type = "subtract" THEN -amount ELSE amount END), 0) as total')
                    ->value('total');

                $totalPaid = (float) DB::table('cash_transactions')
                    ->where('tailor_id', $tailor->id)
                    ->selectRaw('COALESCE(SUM(CASE WHEN direction = "out" THEN amount ELSE -amount END), 0) as total')
                    ->value('total');

                DB::table('tailors')
                    ->where('id', $tailor->id)
                    ->update([
                        'total_paid' => max(0, $totalPaid),
                        'total_due' => max(0, $manualDue - max(0, $totalPaid)),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('tailors', function (Blueprint $table) {
            foreach (['advance', 'total_due', 'total_paid'] as $column) {
                if (Schema::hasColumn('tailors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
