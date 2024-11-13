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
        Schema::table('tb_balasan', function (Blueprint $table) {
            $table->foreign(['id_ulasan'], 'tb_balasan_ibfk_1')->references(['id_ulasan'])->on('tb_ulasan')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_balasan', function (Blueprint $table) {
            $table->dropForeign('tb_balasan_ibfk_1');
        });
    }
};
