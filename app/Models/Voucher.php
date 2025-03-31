<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $table = 'tb_voucher';
    protected $primaryKey = 'id_voucher';

    public $timestamps = true;
    const CREATED_AT = 'tanggal_dibuat';
    const UPDATED_AT = 'tanggal_diperbarui';

    protected $fillable=[
        'kode_voucher',
        'nama_voucher',
        'diskon',
        'min_pembelian',
        'status',
        'tanggal_mulai',
        'tanggal_akhir',
    ];

    public function voucherPelanggan(){
        return $this->hasMany(VoucherPelanggan::class, 'id_voucher');
    }

    public static function updateAllStatuses()
    {
        $now = now();

        // Kadaluarsa
        static::where('tanggal_akhir', '<', $now)
            ->where('status', '!=', 'kadaluarsa')
            ->update(['status' => 'kadaluarsa']);

        // Nonaktif
        static::where('tanggal_mulai', '>', $now)
            ->where('status', '!=', 'nonaktif')
            ->update(['status' => 'nonaktif']);

        // Aktif
        // static::where('tanggal_mulai', '<=', $now)
        //     ->where('tanggal_akhir', '>=', $now)
        //     ->where('status', '!=', 'aktif')
        //     ->update(['status' => 'aktif']);
    }
}
