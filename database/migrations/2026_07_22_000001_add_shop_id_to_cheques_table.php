<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->constrained('shops')->nullOnDelete();
        });

        $firstShopId = DB::table('shops')->orderBy('id')->value('id');

        if ($firstShopId) {
            DB::table('cheques')->whereNull('shop_id')->update(['shop_id' => $firstShopId]);
        }
    }

    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });
    }
};
