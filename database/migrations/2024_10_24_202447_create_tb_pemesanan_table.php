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
        Schema::create('tb_pemesanan', function (Blueprint $table) {
            $table->integer('id_pemesanan', true);
            $table->integer('id_pelanggan')->index('id_user');
            $table->dateTime('tanggal_pemesanan')->useCurrent();
            $table->string('alamat_pengiriman', 1000)->nullable();
            $table->double('total_harga', null, 0)->nullable()->default(0);
            $table->enum('status_pemesanan', ['Keranjang', 'Proses_Pembayaran', 'Dibayar', 'Pesanan_Diterima'])->nullable()->default('Keranjang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pemesanan');
    }
};
