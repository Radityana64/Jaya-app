<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarVariasi extends Model
{
    use HasFactory;
    protected $table = 'tb_gambar_variasi';

    protected $primaryKey = 'id_gambar_variasi';
    public $timestamps = false;

    protected $fillable=[
        'id_produk_variasi',
        'gambar',
        'public_id',
    ];

    public function produkVariasi(){
       return $this->belongsTo(ProdukVariasi::class, 'id_produk_variasi'); 
    }
}
