<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cloth_sewings', function (Blueprint $table) {
            $table->decimal('per_piece_rate', 12, 2)->default(0)->after('item_qty');
            $table->decimal('total_rate', 12, 2)->default(0)->after('per_piece_rate');
        });
    }

    public function down(): void
    {
        Schema::table('cloth_sewings', function (Blueprint $table) {
            $table->dropColumn(['per_piece_rate', 'total_rate']);
        });
    }
};
