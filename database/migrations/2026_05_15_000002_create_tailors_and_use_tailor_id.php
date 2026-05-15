<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tailors')) {
            Schema::create('tailors', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        $this->seedTailorsFromExistingNames();

        Schema::table('cloth_sewings', function (Blueprint $table) {
            if (! Schema::hasColumn('cloth_sewings', 'tailor_id')) {
                $table->foreignId('tailor_id')->nullable()->after('tailor_name')->constrained('tailors')->nullOnDelete();
            }
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_transactions', 'tailor_id')) {
                $table->foreignId('tailor_id')->nullable()->after('sales_man_id')->constrained('tailors')->nullOnDelete();
            }
        });

        $this->backfillTailorIds();

        Schema::table('cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_transactions', 'tailor_name')) {
                $table->dropColumn('tailor_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_transactions', 'tailor_name')) {
                $table->string('tailor_name')->nullable()->after('tailor_id');
            }
        });

        if (Schema::hasColumn('cash_transactions', 'tailor_id')) {
            DB::table('cash_transactions')
                ->whereNotNull('cash_transactions.tailor_id')
                ->select('id', 'tailor_id')
                ->orderBy('id')
                ->each(function ($transaction) {
                    $name = DB::table('tailors')->where('id', $transaction->tailor_id)->value('name');

                    if ($name) {
                        DB::table('cash_transactions')
                            ->where('id', $transaction->id)
                            ->update(['tailor_name' => $name]);
                    }
                });

            Schema::table('cash_transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tailor_id');
            });
        }

        Schema::table('cloth_sewings', function (Blueprint $table) {
            if (Schema::hasColumn('cloth_sewings', 'tailor_id')) {
                $table->dropConstrainedForeignId('tailor_id');
            }
        });

        Schema::dropIfExists('tailors');
    }

    private function seedTailorsFromExistingNames(): void
    {
        $names = collect();

        if (Schema::hasColumn('cloth_sewings', 'tailor_name')) {
            $names = $names->merge(DB::table('cloth_sewings')
                ->whereNotNull('tailor_name')
                ->pluck('tailor_name'));
        }

        if (Schema::hasColumn('cash_transactions', 'tailor_name')) {
            $names = $names->merge(DB::table('cash_transactions')
                ->whereNotNull('tailor_name')
                ->pluck('tailor_name'));
        }

        $names
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->each(fn ($name) => DB::table('tailors')->updateOrInsert(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()]
            ));
    }

    private function backfillTailorIds(): void
    {
        if (Schema::hasColumn('cloth_sewings', 'tailor_name')) {
            DB::table('cloth_sewings')
                ->whereNotNull('tailor_name')
                ->select('id', 'tailor_name')
                ->orderBy('id')
                ->each(function ($sewing) {
                    $tailorId = DB::table('tailors')->where('name', $sewing->tailor_name)->value('id');

                    if ($tailorId) {
                        DB::table('cloth_sewings')
                            ->where('id', $sewing->id)
                            ->update(['tailor_id' => $tailorId]);
                    }
                });
        }

        if (Schema::hasColumn('cash_transactions', 'tailor_name')) {
            DB::table('cash_transactions')
                ->whereNotNull('tailor_name')
                ->select('id', 'tailor_name')
                ->orderBy('id')
                ->each(function ($transaction) {
                    $tailorId = DB::table('tailors')->where('name', $transaction->tailor_name)->value('id');

                    if ($tailorId) {
                        DB::table('cash_transactions')
                            ->where('id', $transaction->id)
                            ->update(['tailor_id' => $tailorId]);
                    }
                });
        }
    }
};
