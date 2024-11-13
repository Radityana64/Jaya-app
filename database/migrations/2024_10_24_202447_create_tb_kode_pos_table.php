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
        Schema::create('tb_kode_pos', function (Blueprint $table) {
            $table->integer('id_kode_pos', true);
            $table->integer('id_kota')->nullable()->index('id_kota');
            $table->integer('kode_pos')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_kode_pos');
    }
};
