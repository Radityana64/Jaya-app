<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;
    protected $table = 'tb_pengiriman';

    protected $primaryKey = 'id_pengiriman';

    public $timestamps = true;

    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';
    
    protected $fillable=[
        'id_pemesanan',
        'kurir',
        'biaya_pengiriman',
        'estimasi_pengiriman',
        'status_pengiriman',
        'tanggal_pengiriman',
        'tanggal_diterima',
    ];
    public function pemesanan(){
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
