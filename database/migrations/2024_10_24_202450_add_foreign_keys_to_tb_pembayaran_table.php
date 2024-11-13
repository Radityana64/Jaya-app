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
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            $table->foreign(['id_pemesanan'], 'fk_pembayaran_pemesanan')->references(['id_pemesanan'])->on('tb_pemesanan')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_pembayaran', function (Blueprint $table) {
            $table->dropForeign('fk_pembayaran_pemesanan');
        });
    }
};
