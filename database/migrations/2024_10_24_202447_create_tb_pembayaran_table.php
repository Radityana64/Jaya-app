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
        Schema::create('tb_pembayaran', function (Blueprint $table) {
            $table->integer('id_pembayaran', true);
            $table->integer('id_pemesanan')->index('id_pemesanan');
            $table->string('id_transaksi_midtrans')->unique('midtrans_transaction_id');
            $table->string('snap_token', 255);
            $table->string('metode_pembayaran', 50);
            $table->double('total_pembayaran', null, 0);
            $table->enum('status_pembayaran', ['Pending', 'Berhasil', 'Expired', 'Gagal'])->default('Pending');
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->timestamp('tanggal_dibuat')->useCurrent();
            $table->timestamp('tanggal_diperbarui')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pembayaran');
    }
};
