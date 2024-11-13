<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_balasan', function (Blueprint $table) {
            $table->integer('id_balasan', true);
            $table->integer('id_ulasan')->nullable()->index('id_ulasan');
            $table->text('balasan')->nullable();
            $table->timestamp('tanggal_dibuat')->nullable()->useCurrent();
            $table->timestamp('tanggal_diperbarui')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_balasan');
    }
};
