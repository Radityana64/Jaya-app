<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodePos extends Model
{
    use HasFactory;
    protected $table = 'tb_kode_pos';

    protected $primaryKey = 'id_kode_pos';
    public $timestamps = false;

    protected $fillable=[
        'id_kota',
        'kode_pos',
    ];

    public function alamat(){
       return $this->hasMany(Alamat::class, 'id_alamat'); 
    }
    public function kota(){
        return $this->belongsTo(Kota::class, 'id_kota');
    }
}
