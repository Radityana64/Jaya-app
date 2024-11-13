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
        Schema::create('tb_kota', function (Blueprint $table) {
            $table->integer('id_kota')->primary();
            $table->integer('id_provinsi')->nullable()->index('id_provinsi');
            $table->enum('tipe_kota', ['Kabupaten', 'Kota'])->nullable();
            $table->string('nama_kota')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_kota');
    }
};
