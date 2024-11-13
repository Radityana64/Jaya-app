<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori1 extends Model
{
    use HasFactory;
    protected $table = 'tb_kategori_1';

    protected $primaryKey = 'id_kategori_1';
    public $timestamps = false;

    protected $fillable=[
        'nama_kategori',
    ];

    public function kategori2(){
       return $this->hasMany(Kategori2::class, 'id_kategori_1'); 
    }
}
