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
        Schema::create('tb_detail_produk', function (Blueprint $table) {
            $table->integer('id_detail_produk', true);
            $table->integer('id_produk')->nullable()->index('id_produk');
            $table->text('deskripsi_detail')->nullable();
            $table->string('url_video', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_detail_produk');
    }
};
