<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeVariasi extends Model
{
    use HasFactory;
    protected $table = 'tb_tipe_variasi';
    protected $primaryKey = 'id_tipe_variasi';

    public $timestamps = false;

    protected $fillable=[
        'nama_tipe',
    ];

    public function opsiVariasi(){
        return $this->hasMany(OpsiVariasi::class, 'id_tipe_variasi');
    }
}
