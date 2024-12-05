<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengiriman;
use App\Models\Pemesanan;
use App\Models\Alamat;
use App\Models\Kota;
use App\Models\Pelanggan;
use App\Models\VoucherPelanggan;
use App\Models\Voucher;
use App\Models\DetailPemesanan;
use App\Models\Provinsi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PengirimanController extends Controller
{
    private $apiKey;
    private $baseUrl;
    private $origin;

    public function __construct()
    {
        $this->apiKey = 'e38f2e04465e24f524e025f12121915c';
        $this->baseUrl = 'https://api.rajaongkir.com/starter/';
        $this->origin = '32';
    }

    public function pilihAlamatPengiriman(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_alamat' => 'required|exists:tb_alamat,id_alamat'
            ]);

            $user = $request->user();
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

            if (!$pelanggan) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'code' => 'USER_UNAUTHORIZED'
                ], 401);
            }

            $alamat = Alamat::with(['kodePos.kota.provinsi'])
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->findOrFail($validated['id_alamat']);

            $pemesanan = $this->getPemesananKeranjang($pelanggan);
            if (!$pemesanan) {
                return response()->json([
                    'error' => 'Keranjang belanja tidak ditemukan',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }

            $pemesanan->update([
                'alamat_pengiriman' => $validated['id_alamat']
            ]);

            return response()->json([
                'message' => 'Alamat pengiriman berhasil dipilih',
                'alamat' => $this->buatAlamatLengkap($alamat)
            ]);

        } catch (\Exception $e) {
            Log::error('Error dalam memilih alamat pengiriman:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan sistem',
                'code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    public function getOpsiPengiriman(Request $request)
    {
        try {
            $user = $request->user();
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

            if (!$pelanggan) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'code' => 'USER_UNAUTHORIZED'
                ], 401);
            }

            $pemesanan = $this->getPemesananKeranjang($pelanggan);
            if (!$pemesanan) {
                return response()->json([
                    'error' => 'Keranjang belanja tidak ditemukan',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }

            if (!$pemesanan->alamat_pengiriman) {
                return response()->json([
                    'error' => 'Alamat pengiriman belum dipilih',
                    'code' => 'SHIPPING_ADDRESS_REQUIRED'
                ], 400);
            }

            $pemesanan = $this->getPemesananKeranjang($pelanggan);
            if (!$pemesanan) {
                return response()->json([
                    'error' => 'Keranjang belanja tidak ditemukan',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }
            // Get the selected address and its city ID
            $alamat = Alamat::with(['kodePos.kota.provinsi'])
                ->findOrFail($pemesanan->alamat_pengiriman);

            $idKotaTujuan = $alamat->kodePos->kota->id_kota;

            $totalBerat = $this->hitungTotalBerat($pemesanan);
            
            // Membuat array untuk menyimpan semua hasil kurir
            $allShippingOptions = [];
            
            // Array kurir yang tersedia
            $couriers = ['jne', 'tiki', 'pos'];
            
            // Melakukan request untuk setiap kurir
            foreach ($couriers as $courier) {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->post($this->baseUrl . 'cost', [
                    'origin' => $this->origin,
                    'destination' => $idKotaTujuan,
                    'weight' => $totalBerat,
                    'courier' => $courier
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    if (isset($result['rajaongkir']['results'][0])) {
                        $allShippingOptions[] = $result['rajaongkir']['results'][0];
                    }
                } else {
                    Log::error('RajaOngkir Response Error for ' . $courier . ': ', $response->json());
                }
            }

            if (empty($allShippingOptions)) {
                return response()->json([
                    'error' => 'Gagal mendapatkan opsi pengiriman',
                    'code' => 'SHIPPING_API_ERROR'
                ], 500);
            }

            // Ambil alamat lengkap
            // $alamat = Alamat::with(['kodePos.kota.provinsi'])
            //     ->find($tempCart->id_alamat);
            
            $alamatLengkap = $alamat ? $this->buatAlamatLengkap($alamat) : null;

            return response()->json([
                'shipping_options' => $allShippingOptions,
                'total_weight' => $totalBerat,
                'shipping_address' => $alamatLengkap
            ]);

        } catch (\Exception $e) {
            Log::error('Error dalam mendapatkan opsi pengiriman:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan sistem',
                'code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    public function pilihJasaPengiriman(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'kurir' => 'required|string',
                'layanan' => 'required|string',
                'estimasi_pengiriman'=> 'required|string',
                'biaya_pengiriman' => 'required|numeric'
            ]);

            $user = $request->user();
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

            if (!$pelanggan) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'code' => 'USER_UNAUTHORIZED'
                ], 401);
            }

            // Get active shopping cart
            $pemesanan = $this->getPemesananKeranjang($pelanggan);
            if (!$pemesanan) {
                return response()->json([
                    'error' => 'Keranjang belanja tidak ditemukan',
                    'code' => 'CART_NOT_FOUND'
                ], 404);
            }

            if (!$pemesanan->alamat_pengiriman) {
                return response()->json([
                    'error' => 'Alamat pengiriman belum dipilih',
                    'code' => 'SHIPPING_ADDRESS_REQUIRED'
                ], 400);
            }

            // Get the complete address
            $alamat = Alamat::with(['kodePos.kota.provinsi'])
                ->findOrFail($pemesanan->alamat_pengiriman);

            // Create shipping record
            $pengiriman = Pengiriman::create([
                'id_pemesanan' => $pemesanan->id_pemesanan,
                'kurir' => $validated['kurir'] . ' ' . $validated['layanan'],
                'biaya_pengiriman' => $validated['biaya_pengiriman'],
                'estimasi_pengiriman' => $validated['estimasi_pengiriman'],
                'status_pengiriman' => 'Belum_Bayar',
            ]);
                
            $alamatLengkap = $this->buatAlamatLengkap($alamat);

            // Update order with shipping address
            $pemesanan->update([
                // 'id_alamat' => $tempCart->id_alamat,
                'alamat_pengiriman' => $alamatLengkap
            ]);


            return response()->json([
                'message' => 'Jasa pengiriman berhasil dipilih',
                'pengiriman' => $pengiriman,
                'alamat_pengiriman' => $alamatLengkap
            ]);

        } catch (\Exception $e) {
            Log::error('Error dalam memilih jasa pengiriman:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan sistem',
                'code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    private function getPemesananKeranjang($pelanggan)
    {
        try {
            $pemesanan = Pemesanan::with(['detailPemesanan.produkVariasi'])
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('status_pemesanan', 'Keranjang')
                ->first();
                
            Log::info('Data Pemesanan:', [
                'pemesanan' => $pemesanan
            ]); // Tambahkan logging untuk debug
                
            return $pemesanan;
        } catch (\Exception $e) {
            Log::error('Error dalam mengambil pemesanan keranjang:', [
                'pelanggan_id' => $pelanggan->id_pelanggan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function hitungTotalBerat($pemesanan)
    {
        return $pemesanan->detailPemesanan->sum(function ($detail) {
            return $detail->produkVariasi->berat * $detail->jumlah;
        });
    }

    private function buatAlamatLengkap($alamat)
    {
        $kodePos = $alamat->kodePos;
        $kota = $kodePos->kota;
        $provinsi = $kota->provinsi;

        return "{$alamat->nama_jalan}, {$alamat->detail_lokasi}, {$kota->nama_kota}, {$provinsi->provinsi}, {$kodePos->kode_pos}";
    }

    public function updateStatusDikirim(Request $request)
    {
        try {
            DB::beginTransaction();

            $pengiriman = Pengiriman::where('id_pengiriman', $request->id_pengiriman)
                ->where('status_pengiriman', 'Dikemas')
                ->first();
            
            if (!$pengiriman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengiriman tidak ditemukan atau status bukan Dikemas'
                ], 404);
            }

            $pengiriman->update([
                'status_pengiriman' => 'Dikirim', // Pastikan tanpa tanda kutip ganda di sini
                'tanggal_pengiriman' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status berhasil diupdate menjadi Dikirim',
                'data' => $pengiriman
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat update: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatusDiterima(Request $request, $id)
    {
        try {
            // Ambil user yang sedang login
            $user = $request->user();
    
            // Cari pelanggan berdasarkan user yang login
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->firstOrFail();
    
            DB::beginTransaction();
    
            // Query pengiriman dengan kondisi tambahan
            $pengiriman = Pengiriman::with(['pemesanan' => function($query) use ($pelanggan) {
                $query->where('id_pelanggan', $pelanggan->id_pelanggan);
            }])
            ->where('id_pengiriman', $id)
            ->where('status_pengiriman', 'Dikirim')
            ->first();
            
            // Jika pengiriman tidak ditemukan
            if (!$pengiriman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pesanan tidak ditemukan atau tidak dapat diproses'
                ], 404);
            }
    
            // Pastikan pesanan milik pelanggan yang login
            if ($pengiriman->pemesanan->id_pelanggan !== $pelanggan->id_pelanggan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses ke pesanan ini'
                ], 403);
            }
            
            // Update status pengiriman
            $pengiriman->update([
                'status_pengiriman' => 'Diterima',
                'tanggal_diterima' => now()
            ]);

            // Update status pemesanan
            $pengiriman->pemesanan->update([
                'status_pemesanan' => 'Pesanan_Diterima'
            ]);

            // Cek dan reset status voucher jika pemesanan tidak menggunakan voucher
            $this->resetVoucherStatus($pengiriman->pemesanan);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Pesanan Diterima',
                'data' => $pengiriman
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function resetVoucherStatus(Pemesanan $pemesanan)
    {
        // Cek apakah pemesanan menggunakan voucher
        if (!$pemesanan->penggunaanVoucher()->exists()) {
            // Ambil semua voucher pelanggan yang terpakai
            $voucherPelanggans = VoucherPelanggan::where('id_pelanggan', $pemesanan->id_pelanggan)
                ->where('status', 'terpakai')
                ->whereHas('voucher', function($query) {
                    $query->where('status', 'aktif'); // Hanya ambil voucher yang berstatus aktif
                })
                ->get();

            foreach ($voucherPelanggans as $voucherPelanggan) {
                // Cek syarat untuk mengatur ulang status voucher
                $syaratPenggunaanUlang = $this->cekSyaratPenggunaanUlang($pemesanan->id_pelanggan, $voucherPelanggan->voucher);

                if ($syaratPenggunaanUlang) {
                    // Reset status voucher
                    $voucherPelanggan->update([
                        'status' => 'belum_terpakai',
                        'jumlah_dipakai' => 0,
                        'tanggal_diperbarui' => now()
                    ]);
                
                }
            }
        }
    }

    private function cekSyaratPenggunaanUlang($idPelanggan, Voucher $voucher)
    {
        // Cek pembelian terakhir tanpa voucher dalam periode tertentu
        $pembelianTanpaVoucher = Pemesanan::where('id_pelanggan', $idPelanggan)
            ->where('status_pemesanan', 'Pesanan_Diterima')
            ->whereDoesntHave('penggunaanVoucher')
            ->where('total_harga', '>=', $voucher->min_pembelian)
            ->exists();
        return $pembelianTanpaVoucher;
    }
}