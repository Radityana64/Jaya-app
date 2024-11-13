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
        Schema::table('tb_pelanggan', function (Blueprint $table) {
            $table->foreign(['id_user'], 'tb_pelanggan_ibfk_1')->references(['id_user'])->on('tb_users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_pelanggan', function (Blueprint $table) {
            $table->dropForeign('tb_pelanggan_ibfk_1');
        });
    }
};
