<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carry_men', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('nid_passport_no')->nullable();
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('advance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('carry_man_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carry_man_id')->constrained('carry_men')->cascadeOnDelete();
            $table->date('date');
            $table->string('marka')->nullable();
            $table->string('document_path')->nullable();
            $table->decimal('bale_qty', 12, 2)->default(0);
            $table->decimal('total_unit_kg', 12, 2)->default(0);
            $table->decimal('rate_per_kg', 12, 2)->default(0);
            $table->decimal('total_rate', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('computer_men', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('advance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('computer_man_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_man_id')->constrained('computer_men')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->date('date');
            $table->decimal('computer_design_qty', 12, 2)->default(0);
            $table->decimal('received_qty', 12, 2)->default(0);
            $table->decimal('rate_per_piece', 12, 2)->default(0);
            $table->decimal('total_rate', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('garey_men', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('nid_passport_no')->nullable();
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('advance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('garey_man_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garey_man_id')->constrained('garey_men')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('qty', 12, 2)->default(0);
            $table->string('unit')->default('goj');
            $table->decimal('rate_per_goj', 12, 2)->default(0);
            $table->decimal('total_rate', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garey_man_work_logs');
        Schema::dropIfExists('garey_men');
        Schema::dropIfExists('computer_man_work_logs');
        Schema::dropIfExists('computer_men');
        Schema::dropIfExists('carry_man_work_logs');
        Schema::dropIfExists('carry_men');
    }
};
