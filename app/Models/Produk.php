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
        'id_kategori',
        'nama_produk',
        'deskripsi',
        'status',
        'tanggal_dibuat',
        'tanggal_diperbarui',
    ];
    
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
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
