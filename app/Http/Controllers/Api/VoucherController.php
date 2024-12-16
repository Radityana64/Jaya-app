<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\VoucherPelanggan;
use App\Models\Pemesanan;
use App\Models\Pelanggan;
use App\Models\PenggunaanVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VoucherController extends Controller
{
    // Membuat Voucher
    public function store(Request $request)
    {
        $requiredFields = ['kode_voucher', 
        'nama_voucher', 
        'diskon', 
        'min_pembelian', 
        'status', 
        'tanggal_mulai', 
        'tanggal_akhir'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }
        $validator = Validator::make($request->all(), [
            'kode_voucher' => 'required|unique:tb_voucher,kode_voucher',
            'nama_voucher' => 'required|string|max:255',
            'diskon' => 'required|numeric|min:0|max:100',
            'min_pembelian' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif,kadaluarsa',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai'
        ]);

        $kodeVoucherConflict = \DB::table('tb_voucher')->where('kode_voucher', $request->kode_voucher)->exists();
    
        if ($kodeVoucherConflict) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conflict. Kode Voucher Sudah Digunakan',
            ], 409); // 409
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $voucher = Voucher::create([
            'kode_voucher' => $request->kode_voucher,
            'nama_voucher' => $request->nama_voucher,
            'diskon' => $request->diskon,
            'min_pembelian' => $request->min_pembelian,
            'status' => $request->status,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_akhir' => $request->tanggal_akhir,
            'tanggal_dibuat' => now(),
            'tanggal_diperbarui' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $voucher
        ], 201);
    }

    // Update Voucher
    public function update($id, Request $request)
    {
        $voucher = Voucher::find($id);

        if(!$voucher){
            return response()->json([
                'status'=>false,
                'message'=>'voucher tidak ditemukan'
            ], 404);
        }

        if ($request->isNotFilled(['kode_voucher', 'nama_voucher', 'diskon', 'min_pemeblian', 'tanggal_mulai', 'tanggal_mulai', 'tanggal_akhir'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. No data provided.',
            ], 400); // 400 Bad Request
        }

        $validator = Validator::make($request->all(), [
            'kode_voucher' => 'sometimes|unique:tb_voucher,kode_voucher,' . $id . ',id_voucher',
            'nama_voucher' => 'sometimes|string|max:255',
            'diskon' => 'sometimes|numeric|min:0|max:100',
            'min_pembelian' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:aktif,nonaktif',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_akhir' => 'sometimes|date|after:tanggal_mulai'
        ]);

        if ($request->has('kode_voucher') && $request->kode_voucher !== $voucher->kode_voucher) {
            $kodeVoucherConflict = \DB::table('tb_voucher')
                ->where('kode_voucher', $request->kode_voucher)
                ->where('id_voucher', '!=', $id)
                ->exists();
        
            if ($kodeVoucherConflict) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Conflict. Kode Voucher Sudah Digunakan',
                ], 409); // 409
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $voucher->update($request->only([
            'kode_voucher', 'nama_voucher', 'diskon', 
            'min_pembelian', 'status', 
            'tanggal_mulai', 'tanggal_akhir'
        ]) + ['tanggal_diperbarui' => now()]);

        return response()->json([
            'success' => true,
            'data' => $voucher
        ]);
    }

    public function distribusiVoucher(Request $request)
    {

        $requiredFields = ['id_voucher', 'kriteria_distribusi'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }

        $validator = Validator::make($request->all(), [
            'id_voucher' => 'required|exists:tb_voucher,id_voucher',
            'kriteria_distribusi' => 'required|in:semua_pelanggan,pelanggan_aktif,pelanggan_loyal',
            // 'min_transaksi' => 'sometimes|numeric|min:0',
            // 'min_jumlah_pesanan' => 'sometimes|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $voucher = Voucher::findOrFail($request->id_voucher);

        // Query untuk mencari pelanggan sesuai kriteria
        $queryPelanggan = Pelanggan::query();

        switch ($request->kriteria_distribusi) {
            case 'semua_pelanggan':
                // Tidak perlu filter tambahan
                break;
            case 'pelanggan_loyal':
                // Pelanggan dengan kriteria khusus
                $queryPelanggan->whereHas('pemesanan', function($query) {
                    $query->where('status_pemesanan', 'Pesanan_Diterima')
                        ->groupBy('id_pelanggan')
                        ->havingRaw('COUNT(*) >= 5')  // Minimal 5 pesanan
                        ->havingRaw('SUM(total_harga) >= 1000000');  // Total transaksi minimal 1 juta
                });
                break;
        }

        // Ambil ID pelanggan yang memenuhi kriteria
        $pelangganIds = $queryPelanggan->pluck('id_pelanggan');

        // Proses distribusi voucher
        $voucherPelanggan = [];
        foreach ($pelangganIds as $idPelanggan) {
            // Cek apakah pelanggan sudah punya voucher ini
            $existingVoucher = VoucherPelanggan::where('id_voucher', $voucher->id_voucher)
                ->where('id_pelanggan', $idPelanggan)
                ->first();

            if (!$existingVoucher) {
                $voucherPelanggan[] = VoucherPelanggan::create([
                    'id_voucher' => $voucher->id_voucher,
                    'id_pelanggan' => $idPelanggan,
                    'status' => 'belum_terpakai',
                    'jumlah_dipakai' => 0,
                    'tanggal_dibuat' => now(),
                    'tanggal_diperbarui' => now()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'total_pelanggan_menerima' => count($voucherPelanggan),
            'voucher_pelanggan' => $voucherPelanggan
        ]);
    }
    
    public function gunakanVoucher(Request $request)
    {
        $requiredFields = ['id_pemesanan', 'id_voucher', 'jumlah_diskon'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }

        $validator = Validator::make($request->all(), [
            'id_pemesanan' => 'required|exists:tb_pemesanan,id_pemesanan',
            'id_voucher' => 'required|exists:tb_voucher,id_voucher',
            'jumlah_diskon' => 'required|min:0'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            return DB::transaction(function () use ($request) {
                // Ambil pemesanan dan voucher
                $pemesanan = Pemesanan::findOrFail($request->id_pemesanan);
                $voucher = Voucher::findOrFail($request->id_voucher);
                $jumlahDiskon = $request->jumlah_diskon;
    
                // Validasi status voucher
                $validasiVoucher = $this->validasiVoucher($voucher, $pemesanan);
                if (!$validasiVoucher['success']) {
                    return response()->json($validasiVoucher, 422);
                }
    
                // Cari voucher pelanggan
                $voucherPelanggan = VoucherPelanggan::where('id_voucher', $voucher->id_voucher)
                    ->where('id_pelanggan', $pemesanan->id_pelanggan)
                    ->where('status', 'belum_terpakai')
                    ->first();
    
                if (!$voucherPelanggan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher tidak ditemukan atau sudah terpakai.'
                    ], 400);
                }
                    
                $prosesVoucher = $this->prosesVoucher($pemesanan, $voucherPelanggan, $voucher, $jumlahDiskon);
                
                if (!$prosesVoucher['success']) {
                    return response()->json($prosesVoucher, 422);
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Voucher berhasil digunakan'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function validasiVoucher(Voucher $voucher, Pemesanan $pemesanan)
    {
        // Cek status pemesanan
        if ($pemesanan->status_pemesanan === 'Pesanan_Diterima') {
            return [
                'success' => false,
                'message' => 'Voucher tidak dapat digunakan pada pesanan yang sudah diterima'
            ];
        }
    
        // Cek status voucher
        if ($voucher->status !== 'aktif') {
            return [
                'success' => false,
                'message' => 'Voucher tidak aktif'
            ];
        }
    
        // Cek rentang tanggal voucher
        $sekarang = Carbon::now();
        if ($sekarang->lt(Carbon::parse($voucher->tanggal_mulai)) || 
            $sekarang->gt(Carbon::parse($voucher->tanggal_akhir))) {
            return [
                'success' => false,
                'message' => 'Voucher sudah kadaluarsa'
            ];
        }
    
        // Cek minimal pembelian
        if ($pemesanan->total_harga < $voucher->min_pembelian) {
            return [
                'success' => false,
                'message' => 'Total pembelian tidak mencukupi untuk voucher ini'
            ];
        }
    
        // Cek voucher pelanggan
        $voucherPelanggan = VoucherPelanggan::where('id_voucher', $voucher->id_voucher)
            ->where('id_pelanggan', $pemesanan->id_pelanggan)
            ->first();
    
        // Validasi tambahan untuk penggunaan berulang
        if ($voucherPelanggan->status === 'terpakai') {
            return [
                'success' => false,
                'message' => 'Belum memenuhi syarat penggunaan ulang voucher'
            ];
        }
    
        return [
            'success' => true
        ];
    }
    
    public function prosesVoucher(Pemesanan $pemesanan, VoucherPelanggan $voucherPelanggan, Voucher $voucher, $jumlahDiskon)
    {
        $calculatedVoucherDiscount = round(($voucher->diskon / 100) * $pemesanan->total_harga, 2);
    
        // Validasi jumlah diskon yang diinput
        if (round($jumlahDiskon, 2) != $calculatedVoucherDiscount) {
            return [
                'success' => false,
                'message' => 'Jumlah diskon tidak sesuai'
            ];
        }
    
        try {
            // Catat penggunaan voucher
            $penggunaanVoucher = PenggunaanVoucher::create([
                'id_voucher_pelanggan' => $voucherPelanggan->id_voucher_pelanggan,
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'jumlah_diskon' => $calculatedVoucherDiscount,
                'tanggal_pemakaian' => now()
            ]);
    
            // Update status voucher pelanggan
            $voucherPelanggan->update([
                'status' => 'terpakai',
                'tanggal_diperbarui' => now()
            ]);
    
            // Update total harga pemesanan
            $pemesanan->total_harga -= $calculatedVoucherDiscount;
            $pemesanan->save();
    
            return [
                'success' => true,
                'data' => $penggunaanVoucher
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memproses voucher: ' . $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    // Mendapatkan voucher yang aktif untuk pelanggan yang sedang login
    public function getActiveVouchersForCustomer(Request $request)
    {
        $user = $request->user();
        $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        $activeVouchers = VoucherPelanggan::whereHas('voucher', function($query) use ($pelanggan){
            $query->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('status', 'aktif')
                ->where('tanggal_mulai', '<=', now())
                ->where('tanggal_akhir', '>=', now());
            })
            ->where('status', 'belum_terpakai')
            ->with('voucher')
            ->get();

        if ($activeVouchers->isEmpty()) {
            return response()->json([
                'status' => false,
                'message'=> 'Pelanggan Tidak memiliki voucher yang bisa digunakan'
            ],404);
        }
        return response()->json([
            'success' => true,
            'data' => $activeVouchers
        ]);
    }

    // Dapatkan Voucher Tersedia untuk Pelanggan
    public function getVoucherById($id_voucher)
    {
        // Cek apakah voucher ada
        $voucher = Voucher::find($id_voucher);
        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }

        // Ambil voucher dengan informasi pelanggan yang memiliki voucher
        $voucherDetail = Voucher::with(['voucherPelanggan' => function($query) {
            $query->with('pelanggan');
        }])
        ->where('id_voucher', $id_voucher)
        ->first();

        // Transformasi data untuk respons
        $voucherPelanggan = $voucherDetail->voucherPelanggan->map(function($item) {
            return [
                'id_voucher_pelanggan' => $item->id_voucher_pelanggan,
                'pelanggan' => [
                    'id_pelanggan' => $item->pelanggan->id_pelanggan,
                    'nama_pelanggan' => $item->pelanggan->nama_pelanggan
                ],
                'status_voucher_pelanggan' => $item->status,
                'tanggal_dibuat' => $item->tanggal_dibuat,
                'tanggal_diperbarui' => $item->tanggal_diperbarui
            ];
        });

        return response()->json([
            'success' => true,
            'voucher' => [
                'id_voucher' => $voucher->id_voucher,
                'kode_voucher' => $voucher->kode_voucher,
                'nama_voucher' => $voucher->nama_voucher,
                'diskon' => $voucher->diskon,
                'min_pembelian' => $voucher->min_pembelian,
                'status' => $voucher->status,
                'tanggal_mulai' => $voucher->tanggal_mulai,
                'tanggal_akhir' => $voucher->tanggal_akhir
            ],
            'pelanggan_voucher' => $voucherPelanggan
        ]);
    }

    // Mendapatkan semua voucher
    public function getAllVouchers()
    {
        try {
            $vouchers = Voucher::all();

            return response()->json([
                'success' => true,
                'data' => $vouchers
            ]);
        } catch (\Exception $e) {
            return response()->json([
               'status'=> 'error',
               'message'=>'data tidak dapat diambil'. $e->getMessage()  
            ], 500);
        }
        
    }

    // Mendapatkan voucher yang dimiliki oleh pelanggan
    // public function getAllActiveVouchers()
    // {
    //     // Mengambil semua data dari voucher pelanggan yang statusnya aktif dengan relasi voucher
    //     $vouchers = VoucherPelanggan::whereHas('voucher', function($query) {
    //             $query->where('status', 'aktif')
    //                 ->where('tanggal_mulai', '<=', now())
    //                 ->where('tanggal_akhir', '>=', now());
    //         })
    //         ->with('voucher')
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $vouchers
    //     ]);
    // }

    // Hapus Voucher
    public function nonaktif(Request $request, $id)
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:aktif,nonaktif'            
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        
        // Cek apakah voucher sudah pernah digunakan
        $voucher->update([
            'status' => 'nonaktif',
            'tanggal_diperbarui' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dinonaktifkan'
        ]);
    }

}
