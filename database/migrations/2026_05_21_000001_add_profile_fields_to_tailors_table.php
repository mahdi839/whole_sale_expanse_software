<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tailors', function (Blueprint $table) {
            if (! Schema::hasColumn('tailors', 'phone')) {
                $table->string('phone', 30)->nullable()->after('name');
            }

            if (! Schema::hasColumn('tailors', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('tailors', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('address');
            }

            if (! Schema::hasColumn('tailors', 'document_path')) {
                $table->string('document_path')->nullable()->after('profile_picture');
            }
        });

        Schema::table('cloth_sewings', function (Blueprint $table) {
            if (Schema::hasColumn('cloth_sewings', 'tailor_name')) {
                $table->string('tailor_name')->nullable()->change();
            }
        });

        Schema::table('received_cloths', function (Blueprint $table) {
            if (Schema::hasColumn('received_cloths', 'tailor_name')) {
                $table->string('tailor_name')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tailors', function (Blueprint $table) {
            foreach (['document_path', 'profile_picture', 'address', 'phone'] as $column) {
                if (Schema::hasColumn('tailors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
