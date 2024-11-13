<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPemesanan extends Model
{
    use HasFactory;
    protected $table = 'tb_detail_pemesanan';
    
    protected $primaryKey = 'id_detail_pemesanan';

    public $timestamps = false;

    protected $fillable=[
        'id_pemesanan',
        'id_produk_variasi',
        'jumlah',
        'sub_total_produk',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
    public function produkVariasi()
    {
        return $this->belongsTo(ProdukVariasi::class, 'id_produk_variasi');
    }
}
