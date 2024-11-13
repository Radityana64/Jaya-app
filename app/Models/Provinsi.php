<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;
    protected $table = 'tb_provinsi';

    protected $primaryKey = 'id_provinsi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_provinsi',
        'provinsi',
    ];

    public function kota()
    {
        return $this->hasMany(Kota::class, 'id_provinsi'); 
    }
}
