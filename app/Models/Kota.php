<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    use HasFactory;
    protected $table = 'tb_kota';

    protected $primaryKey = 'id_kota';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable=[
        'id_kota',
        'id_provinsi',
        'tipe_kota',
        'nama_kota',
    ];

    public function kodePos(){
       return $this->hasMany(KodePos::class, 'id_kota'); 
    }
    public function provinsi(){
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }
    public function tempShippingCarts()
    {
        return $this->hasMany(TempShippingCart::class, 'id_kota', 'id_kota');
    }
}
