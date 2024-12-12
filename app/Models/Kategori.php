<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;
    protected $table = 'tb_kategori';

    protected $primaryKey = 'id_kategori';
    public $timestamps = false;

    protected $fillable=[
        'nama_kategori',
        'gambar_kategori',
        'id_induk',
        'level',
        'status',
    ];

    public function subKategori()
    {
        return $this->hasMany(Kategori::class, 'id_induk', 'id_kategori')
            ->where('level', '2');
    }

    // Relasi induk
    public function induk()
    {
        return $this->belongsTo(Kategori::class, 'id_induk', 'id_kategori');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori');
    }
  
}
  // public function kategori1(){
    //     return $this->belongsTo(Kategori1::class, 'id_kategori_1');
    // }