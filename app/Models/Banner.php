<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $table ='tb_banner';

    protected $primaryKey ='id_banner';

    public $timestamps = true;

    protected $fillable=[
        'judul',
        'gambar_banner',
        'deskripsi',
        'status',
    ];
}
