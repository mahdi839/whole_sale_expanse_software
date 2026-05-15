<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'sales_man_id')) {
                $table->foreignId('sales_man_id')->nullable()->after('category')->constrained('sales_men')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'sales_man_id')) {
                $table->dropConstrainedForeignId('sales_man_id');
            }
        });
    }
};
