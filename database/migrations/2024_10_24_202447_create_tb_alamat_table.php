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
        Schema::create('tb_alamat', function (Blueprint $table) {
            $table->integer('id_alamat', true);
            $table->integer('id_pelanggan')->nullable()->index('id_pelanggan');
            $table->integer('id_kode_pos')->nullable()->index('id_kode_pos');
            $table->string('nama_jalan')->nullable();
            $table->string('detail_lokasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_alamat');
    }
};
