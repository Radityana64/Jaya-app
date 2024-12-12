<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukVariasi extends Model
{
    use HasFactory;

    protected $table = "tb_produk_variasi";

    protected $primaryKey = 'id_produk_variasi';
    public $timestamps = true; // Menandakan bahwa model menggunakan timestamps

    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';
    
    protected $fillable = [
        'id_produk',
        'nama_produk',
        'stok',
        'berat',
        'hpp',
        'harga',
        'status',
        'tanggal_dibuat',
        'tanggal_diperbarui',
        'default',
    ];
    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_produk');
    }
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
    public function gambarVariasi()
    {
        return $this->hasMany(GambarVariasi::class, 'id_produk_variasi');
    }
    public function detailProdukVariasi()
    {
        return $this->hasMany(DetailProdukVariasi::class, 'id_produk_variasi');
    }
    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_produk_variasi');
    }
}
