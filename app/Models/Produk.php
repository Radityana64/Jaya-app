<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = "tb_produk";

    protected $primaryKey = 'id_produk';
    public $timestamps = true; // Menandakan bahwa model menggunakan timestamps

    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';
    
    protected $fillable = [
        'id_kategori_2',
        'nama_produk',
        'deskripsi',
        'tanggal_dibuat',
        'tanggal_diperbarui',
    ];
    
    public function kategori2()
    {
        return $this->belongsTo(Kategori2::class, 'id_kategori_2');
    }
    public function gambarProduk()
    {
        return $this->hasMany(GambarProduk::class, 'id_produk');
    }
    public function detailProduk()
    {
        return $this->hasOne(DetailProduk::class, 'id_produk');
    }
    public function produkVariasi()
    {
        return $this->hasMany(produkVariasi::class, 'id_produk');
    }
}
