<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogProduk extends Model
{
    use HasFactory;
    protected $table = "tb_log_produk";

    protected $primaryKey = 'id_log_produk';
    public $timestamps = true; // Menandakan bahwa model menggunakan timestamps

    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';
    
    protected $fillable = [
        'id_produk',
        'id_user',
        'jumlah_produk',
        'berat',
        'HPP',
        'tanggal_dibuat',
        'tanggal_diperbarui'
    ];
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

}
