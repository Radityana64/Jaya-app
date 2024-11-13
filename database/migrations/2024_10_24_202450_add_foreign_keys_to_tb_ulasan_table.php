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
        Schema::table('tb_ulasan', function (Blueprint $table) {
            $table->foreign(['id_rating'], 'tb_ulasan_ibfk_1')->references(['id_rating'])->on('tb_rating')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_pemesanan'], 'tb_ulasan_ibfk_2')->references(['id_pemesanan'])->on('tb_pemesanan')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_produk'], 'tb_ulasan_ibfk_3')->references(['id_produk'])->on('tb_produk')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_ulasan', function (Blueprint $table) {
            $table->dropForeign('tb_ulasan_ibfk_1');
            $table->dropForeign('tb_ulasan_ibfk_2');
            $table->dropForeign('tb_ulasan_ibfk_3');
        });
    }
};
