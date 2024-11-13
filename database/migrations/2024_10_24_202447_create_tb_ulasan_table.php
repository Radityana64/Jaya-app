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
        Schema::create('tb_ulasan', function (Blueprint $table) {
            $table->integer('id_ulasan', true);
            $table->integer('id_rating')->index('id_rating');
            $table->integer('id_produk')->nullable()->index('id_produk');
            $table->integer('id_pemesanan')->nullable()->index('tb_ulasan_ibfk_2');
            $table->text('ulasan')->nullable();
            $table->timestamp('tanggal_dibuat')->useCurrent();
            $table->timestamp('tanggal_diperbarui')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ulasan');
    }
};
