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
        Schema::create('tb_log_produk', function (Blueprint $table) {
            $table->integer('id_log_produk', true);
            $table->integer('id_produk')->index('id_produk');
            $table->integer('id_user')->index('id_user');
            $table->integer('jumlah_produk')->nullable();
            $table->float('berat', null, 0)->nullable();
            $table->float('HPP', null, 0)->nullable();
            $table->timestamp('tanggal_dibuat')->nullable()->useCurrent();
            $table->timestamp('tanggal_diperbarui')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_log_produk');
    }
};
