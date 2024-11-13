<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarProduk extends Model
{
    use HasFactory;
    protected $table = 'tb_gambar_produk';

    protected $primaryKey = 'id_gambar';
    public $timestamps = false;

    protected $fillable=[
        'id_produk',
        'gambar',
        'public_id',
    ];

    public function produk(){
       return $this->belongsTo(Produk::class, 'id_produk'); 
    }
}
