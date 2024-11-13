<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailProduk extends Model
{
    use HasFactory;
    protected $table = 'tb_detail_produk';

    protected $primaryKey = 'id_detail_produk';
    public $timestamps = false;

    protected $fillable=[
        'id_produk',
        'deskripsi_detail',
        'url_video',
    ];

    public function produk(){
       return $this->belongsTo(Produk::class, 'id_produk'); 
    }
}
