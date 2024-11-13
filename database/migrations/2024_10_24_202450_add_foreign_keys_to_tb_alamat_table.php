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
        Schema::table('tb_alamat', function (Blueprint $table) {
            $table->foreign(['id_kode_pos'], 'tb_alamat_ibfk_1')->references(['id_kode_pos'])->on('tb_kode_pos')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_pelanggan'], 'tb_alamat_ibfk_2')->references(['id_pelanggan'])->on('tb_pelanggan')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_alamat', function (Blueprint $table) {
            $table->dropForeign('tb_alamat_ibfk_1');
            $table->dropForeign('tb_alamat_ibfk_2');
        });
    }
};
