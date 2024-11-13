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
        Schema::create('tb_detail_pemesanan', function (Blueprint $table) {
            $table->integer('id_detail_pemesanan', true);
            $table->integer('id_pemesanan')->index('id_pemesanan');
            $table->integer('id_produk')->index('id_produk');
            $table->integer('jumlah');
            $table->double('sub_total_produk', null, 0)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_detail_pemesanan');
    }
};
