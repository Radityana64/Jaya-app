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
        Schema::table('tb_log_produk', function (Blueprint $table) {
            $table->foreign(['id_produk'], 'tb_log_produk_ibfk_1')->references(['id_produk'])->on('tb_produk')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_user'], 'tb_log_produk_ibfk_2')->references(['id_user'])->on('tb_users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_log_produk', function (Blueprint $table) {
            $table->dropForeign('tb_log_produk_ibfk_1');
            $table->dropForeign('tb_log_produk_ibfk_2');
        });
    }
};
