<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenggunaanVoucher extends Model
{
    use HasFactory;
    protected $table = 'tb_penggunaan_voucher';
    protected $primaryKey = 'id_penggunaan_voucher';

    public $timestamps = false;
    // const CREATED_AT = 'tanggal_dibuat';
    // const UPDATED_AT = 'tanggal_diperbarui';

    protected $fillable=[
        'id_voucher_pelanggan',
        'id_pemesanan',
        'tanggal_pemakaian',
    ];

    public function voucherPelanggan(){
        return $this->belongsTo(VoucherPelanggan::class, 'id_voucher_pelanggan');
    }
    public function pemesanan(){
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
