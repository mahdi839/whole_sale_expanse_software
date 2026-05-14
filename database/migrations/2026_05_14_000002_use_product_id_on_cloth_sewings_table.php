<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cloth_sewings', function (Blueprint $table) {
            if (! Schema::hasColumn('cloth_sewings', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('tailor_name')->constrained('products')->nullOnDelete();
            }
        });

        Schema::table('cloth_sewings', function (Blueprint $table) {
            if (Schema::hasColumn('cloth_sewings', 'product_name')) {
                $table->dropColumn('product_name');
            }

            if (Schema::hasColumn('cloth_sewings', 'design_code')) {
                $table->dropColumn('design_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cloth_sewings', function (Blueprint $table) {
            if (! Schema::hasColumn('cloth_sewings', 'product_name')) {
                $table->string('product_name')->nullable()->after('tailor_name');
            }

            if (! Schema::hasColumn('cloth_sewings', 'design_code')) {
                $table->string('design_code')->nullable()->after('product_name');
            }

            if (Schema::hasColumn('cloth_sewings', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });
    }
};
