<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    use HasFactory;
    protected $table = 'tb_alamat';

    protected $primaryKey = 'id_alamat';

    public $timestamps = false;

    protected $fillable=[
        'id_pelanggan', 
        'id_kode_pos',
        'nama_jalan',
        'detail_lokasi',
    ];

    public function kodePos(){
        return $this->belongsTo(KodePos::class, 'id_kode_pos'); 
    }
    public function pelanggan(){
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    public function tempShippingCarts()
    {
        return $this->hasMany(TempShippingCart::class, 'id_alamat', 'id_alamat');
    }
}
