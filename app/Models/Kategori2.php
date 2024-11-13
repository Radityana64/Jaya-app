<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori2 extends Model
{
    use HasFactory;
    protected $table = 'tb_kategori_2';

    protected $primaryKey = 'id_kategori_2';
    public $timestamps = false;

    protected $fillable=[
        'id_kategori_1',
        'nama_kategori',
    ];

    public function produk(){
       return $this->hasMany(Produk::class, 'id_kategori_2'); 
    }
    public function kategori1(){
        return $this->belongsTo(Kategori1::class, 'id_kategori_1');
    }
}
