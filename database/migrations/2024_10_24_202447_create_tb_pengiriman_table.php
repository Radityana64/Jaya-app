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
        Schema::create('tb_pengiriman', function (Blueprint $table) {
            $table->integer('id_pengiriman', true);
            $table->integer('id_pemesanan')->nullable()->index('id_pemesanan');
            $table->string('kurir')->nullable();
            $table->double('biaya_pengiriman', null, 0)->nullable();
            $table->enum('status_pengiriman', ['Diterima', 'Dikemas', 'Dikirim', 'Belum_Bayar', ''])->nullable()->default('Belum_Bayar');
            $table->timestamp('tanggal_pengiriman')->nullable();
            $table->timestamp('tanggal_diterima')->nullable();
            $table->timestamp('tanggal_dibuat')->useCurrent();
            $table->timestamp('tanggal_diperbarui')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pengiriman');
    }
};
