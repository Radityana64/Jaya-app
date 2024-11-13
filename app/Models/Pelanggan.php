<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends model
{
    use HasFactory;

    protected $table = 'tb_pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $timestamps = true;
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';

    protected $fillable = [
        'id_user',
        'username',
        'telepon',
        'tanggal_dibuat',
        'tanggal_diperbarui'
    ];

    public function pemesanan(){
        return $this->hasMany(Pemesanan::class, 'id_pelanggan');
    }
    public function alamat(){
        return $this->hasMany(Alamat::class, 'id_pelanggan');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user',);
    }
    public function voucherPelanggan(){
        return $this->hasMany(VoucherPelanggan::class, 'id_pelanggan');
    }
}
