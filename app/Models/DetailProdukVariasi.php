<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailProdukVariasi extends Model
{
    use HasFactory;
    protected $table = 'tb_detail_produk_variasi';
    protected $primaryKey = 'id_detail_produk_variasi';

    public $timestamps = false;

    protected $fillable=[
        'id_produk_variasi',
        'id_opsi_variasi',
    ];

    public function produkVariasi(){
        return $this->belongsTo(ProdukVariasi::class, 'id_produk_variasi');
    }
    public function opsiVariasi(){
        return $this->belongsTo(OpsiVariasi::class, 'id_opsi_variasi');
    }
}
