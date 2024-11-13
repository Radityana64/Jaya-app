<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'tb_pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = true;

    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';
    
    protected $fillable=[
        'id_pemesanan',
        'id_transaksi_midtrans',
        'snap_token',
        'metode_pembayaran',
        'total_pembayaran',
        'status_pembayaran',
        'waktu_pembayaran',
    ];
    public function pemesanan(){
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
