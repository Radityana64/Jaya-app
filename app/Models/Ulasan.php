<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;
    protected $table = 'tb_ulasan';
    protected $primaryKey = 'id_ulasan';

    public $timestamps = true;
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';

    protected $fillable=[
        'id_rating',
        'id_produk_variasi',
        'id_pemesanan',
        'ulasan',
    ];

    public function rating(){
        return $this->belongsTo(Rating::class, 'id_rating');
    }
    public function produkVariasi(){
        return $this->belongsTo(Produk::class, 'id_produk_variasi');
    }
    public function pemesanan(){
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
    public function balasan(){
        return $this->hasMany(Balasan::class, 'id_ulasan');
    }
}
