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
        Schema::table('tb_kota', function (Blueprint $table) {
            $table->foreign(['id_provinsi'], 'tb_kota_ibfk_1')->references(['id_provinsi'])->on('tb_provinsi')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_kota', function (Blueprint $table) {
            $table->dropForeign('tb_kota_ibfk_1');
        });
    }
};
