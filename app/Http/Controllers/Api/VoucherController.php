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
        $validator = Validator::make($request->all(), [
            'kode_voucher' => 'required|unique:tb_voucher,kode_voucher',
            'nama_voucher' => 'required|string|max:255',
            'diskon' => 'required|numeric|min:0|max:100',
            'min_pembelian' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif,kadaluarsa',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
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
    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode_voucher' => 'sometimes|unique:tb_voucher,kode_voucher,' . $id . ',id_voucher',
            'nama_voucher' => 'sometimes|string|max:255',
            'diskon' => 'sometimes|numeric|min:0|max:100',
            'min_pembelian' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:aktif,nonaktif,kadaluarsa',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_akhir' => 'sometimes|date|after:tanggal_mulai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
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
            ], 400);
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
        $validator = Validator::make($request->all(), [
            'id_pemesanan' => 'required|exists:tb_pemesanan,id_pemesanan',
            'id_voucher' => 'required|exists:tb_voucher,id_voucher'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        return DB::transaction(function () use ($request) {
            // Ambil pemesanan dan voucher
            $pemesanan = Pemesanan::findOrFail($request->id_pemesanan);
            $voucher = Voucher::findOrFail($request->id_voucher);

            // Validasi status voucher
            $this->validasiVoucher($voucher, $pemesanan);

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
                
            $this->prosesVoucher($pemesanan, $voucherPelanggan, $voucher);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil digunakan'
            ]);
        });
    }

    private function validasiVoucher(Voucher $voucher, Pemesanan $pemesanan)
    {
        // Cek status voucher
        if ($voucher->status !== 'aktif') {
            throw new \Exception('Voucher tidak aktif');
        }

        // Cek rentang tanggal voucher
        $sekarang = Carbon::now();
        if ($sekarang->lt(Carbon::parse($voucher->tanggal_mulai)) || 
            $sekarang->gt(Carbon::parse($voucher->tanggal_akhir))) {
            throw new \Exception('Voucher sudah kadaluarsa');
        }

        // Cek minimal pembelian
        if ($pemesanan->total_harga < $voucher->min_pembelian) {
            throw new \Exception('Total pembelian tidak mencukupi untuk voucher ini');
        }

        // Cek voucher pelanggan
        $voucherPelanggan = VoucherPelanggan::where('id_voucher', $voucher->id_voucher)
            ->where('id_pelanggan', $pemesanan->id_pelanggan)
            ->first();

        // Validasi tambahan untuk penggunaan berulang
        if ($voucherPelanggan->status === 'terpakai') {
            throw new \Exception('Belum memenuhi syarat penggunaan ulang voucher');
        }

        return true;
    }    
    
    // Proses Penggunaan Voucher
    private function prosesVoucher(Pemesanan $pemesanan, VoucherPelanggan $voucherPelanggan, Voucher $voucher)
    {
        // Catat penggunaan voucher
        $penggunaanVoucher = PenggunaanVoucher::create([
            'id_voucher_pelanggan' => $voucherPelanggan->id_voucher_pelanggan,
            'id_pemesanan' => $pemesanan->id_pemesanan,
            'tanggal_pemakaian' => now()
        ]);

        // Update status voucher pelanggan
        $voucherPelanggan->update([
            'status' => 'terpakai',
            'tanggal_diperbarui' => now()
        ]);

        return $penggunaanVoucher;
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
            ->with('voucher')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activeVouchers
        ]);
    }

    // Dapatkan Voucher Tersedia untuk Pelanggan
    public function getVoucherTersedia($idPelanggan)
    {
        $voucherTersedia = Voucher::where('status', 'aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_akhir', '>=', now())
            ->whereDoesntHave('voucherPelanggan', function($query) use ($idPelanggan) {
                $query->where('id_pelanggan', $idPelanggan)
                      ->where('status', 'terpakai');
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $voucherTersedia
        ]);
    }

    // Mendapatkan semua voucher
    public function getAllVouchers()
    {
        $vouchers = Voucher::all();

        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    // Mendapatkan voucher yang dimiliki oleh pelanggan
    public function getAllActiveVouchers()
    {
        // Mengambil semua data dari voucher pelanggan yang statusnya aktif dengan relasi voucher
        $vouchers = VoucherPelanggan::whereHas('voucher', function($query) {
                $query->where('status', 'aktif')
                    ->where('tanggal_mulai', '<=', now())
                    ->where('tanggal_akhir', '>=', now());
            })
            ->with('voucher')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    // Hapus Voucher
    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);

        // Cek apakah voucher sudah pernah digunakan
        $sudahDigunakan = VoucherPelanggan::where('id_voucher', $id)
            ->where('status', 'terpakai')
            ->exists();

        if ($sudahDigunakan) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak dapat dihapus karena sudah pernah digunakan'
            ], 400);
        }

        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus'
        ]);
    }

}
