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
        Schema::table('tb_kode_pos', function (Blueprint $table) {
            $table->foreign(['id_kota'], 'tb_kode_pos_ibfk_1')->references(['id_kota'])->on('tb_kota')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_kode_pos', function (Blueprint $table) {
            $table->dropForeign('tb_kode_pos_ibfk_1');
        });
    }
};
