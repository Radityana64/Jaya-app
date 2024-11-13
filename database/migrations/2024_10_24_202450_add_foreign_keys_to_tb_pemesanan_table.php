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
        Schema::table('tb_pemesanan', function (Blueprint $table) {
            $table->foreign(['id_pelanggan'], 'fk_pemesanan_user')->references(['id_pelanggan'])->on('tb_pelanggan')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_pemesanan', function (Blueprint $table) {
            $table->dropForeign('fk_pemesanan_user');
        });
    }
};
