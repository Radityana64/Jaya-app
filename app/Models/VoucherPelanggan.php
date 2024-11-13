<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherPelanggan extends Model
{
    use HasFactory;
    protected $table = 'tb_voucher_pelanggan';
    protected $primaryKey = 'id_voucher_pelanggan';

    public $timestamps = true;
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';

    protected $fillable=[
        'id_voucher',
        'id_pelanggan',
        'status',
    ];

    public function voucher(){
        return $this->belongsTo(Voucher::class, 'id_voucher');
    }
    public function pelanggan(){
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    public function penggunaanVoucher(){
        return $this->hasMany(PenggunaanVoucher::class, 'id_voucher_pelanggan');
    }
}
