<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balasan extends Model
{
    use HasFactory;
    protected $table = 'tb_balasan';

    protected $primaryKey = 'id_balasan';
    public $timestamps = true;

    const CREATED_AT = 'tanggal_dibuat'; 
    const UPDATED_AT = 'tanggal_diperbarui';

    protected $fillable=[
        'id_ulasan',
        'balasan',
    ];

    public function ulasan()
    {
        return $this->belongsTo(Ulasan::class, 'id_ulasan');
    }

}
