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
        Schema::table('tb_detail_pemesanan', function (Blueprint $table) {
            $table->foreign(['id_pemesanan'], 'fk_detail_pemesanan_pemesanan')->references(['id_pemesanan'])->on('tb_pemesanan')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_produk'], 'fk_detail_pemesanan_produk')->references(['id_produk'])->on('tb_produk')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_detail_pemesanan', function (Blueprint $table) {
            $table->dropForeign('fk_detail_pemesanan_pemesanan');
            $table->dropForeign('fk_detail_pemesanan_produk');
        });
    }
};
