<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;
    protected $table = 'tb_pemesanan';
    protected $primaryKey = 'id_pemesanan';

    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'tanggal_pemesanan',
        'total_harga',
        'alamat_pengiriman',
        'status_pemesanan',
    ];
    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pemesanan');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    public function pengiriman() 
    {
        return $this->hasOne(Pengiriman::class, 'id_pemesanan');
    }
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pemesanan');
    }
    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_pemesanan');
    }
    public function penggunaanVoucher()
    {
        return $this->hasOne(PenggunaanVoucher::class, 'id_pemesanan');
    }
}
