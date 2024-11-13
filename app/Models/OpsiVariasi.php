<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpsiVariasi extends Model
{
    use HasFactory;
    protected $table = 'tb_opsi_variasi';
    protected $primaryKey = 'id_opsi_variasi';

    public $timestamps = false;

    protected $fillable=[
        'id_tipe_variasi',
        'nama_opsi',
    ];

    public function detailProdukVariasi(){
        return $this->hasMany(DetailProdukVariasi::class, 'id_opsi_variasi');
    }
    public function tipeVariasi(){
        return $this->belongsTo(TipeVariasi::class, 'id_tipe_variasi');
    }
}
