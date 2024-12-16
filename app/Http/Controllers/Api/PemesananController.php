<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use App\Models\ProdukVariasi;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Alamat;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

class PemesananController extends Controller
{
    public function GetKeranjang(Request $request)
    {
        $pelanggan = Auth::user()->pelanggan;

        // Ambil pemesanan dengan detail produk dan variasi
        $pemesanan = Pemesanan::with('detailPemesanan.produkVariasi.gambarVariasi', 'detailPemesanan.produkVariasi.produk.gambarProduk', 
        'detailPemesanan.produkVariasi.detailProdukVariasi.opsiVariasi.tipeVariasi')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('status_pemesanan', 'Keranjang')
            ->first();

        if(!$pemesanan){
            return response()->json([
                'message'=>'Tidak Ada Produk Dalam Keranjang',
            ],404);
        }

        // Format data untuk respons
        $formattedCart = [
            'id_pemesanan' => $pemesanan->id_pemesanan,
            'id_pelanggan' => $pemesanan->id_pelanggan,
            'tanggal_pemesanan' => $pemesanan->tanggal_pemesanan,
            'alamat_pengiriman' => $pemesanan->alamat_pengiriman,
            'total_harga' => $pemesanan->total_harga,
            'status_pemesanan' => $pemesanan->status_pemesanan,
            'detail_pemesanan' => []
        ];

        foreach ($pemesanan->detailPemesanan as $detail) {
            // Ambil gambar variasi, jika tidak ada ambil gambar produk pertama
            $gambarVariasi = $detail->produkVariasi->gambarVariasi->first() ? 
                $detail->produkVariasi->gambarVariasi->first()->gambar : 
                $detail->produkVariasi->produk->gambarProduk->first()->gambar;

            // Membuat string variasi
            $variations = [];
            foreach ($detail->produkVariasi->detailProdukVariasi as $produkVariasi) {
                $variations[] = $produkVariasi->opsiVariasi->tipeVariasi->nama_tipe . ': ' . $produkVariasi->opsiVariasi->nama_opsi;
            }
            $variationsString = implode(', ', $variations);

            $formattedCart['detail_pemesanan'][] = [
                'id_detail_pemesanan' => $detail->id_detail_pemesanan,
                'id_produk_variasi' => $detail->id_produk_variasi,
                'id_pemesanan' => $detail->id_pemesanan,
                'jumlah' => $detail->jumlah,
                'sub_total_produk' => $detail->sub_total_produk,
                'produk_variasi' => [
                    'nama_produk' => $detail->produkVariasi->produk->nama_produk,
                    'id_produk_variasi' => $detail->produkVariasi->id_produk_variasi,
                    'id_produk' => $detail->produkVariasi->id_produk,
                    'stok' => $detail->produkVariasi->stok,
                    'berat' => $detail->produkVariasi->berat,
                    'harga' => $detail->produkVariasi->harga,
                    'gambar' => $gambarVariasi,
                    'variasi' => $variationsString // Menambahkan string variasi di sini
                ]
            ];
        }

        return response()->json(['cart' => $formattedCart]);
    }

    public function TambahKeKeranjang(Request $request)
    {
        try{

            if (!$request->has('id_produk_variasi') || !$request->has('jumlah')) {
                return response()->json([
                    'error' => 'tidak lengkap',
                    'message' => 'id_produk_variasi dan jumlah harus disertakan'
                ], 400);
            }
    
            $user = $request->user();
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();
            
            $produk = ProdukVariasi::where('id_produk_variasi', $request->id_produk_variasi)->first();
            if (!$produk) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk variasi tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'id_produk_variasi'=>'required|exists:tb_produk_variasi,id_produk_variasi',
                'jumlah'=>['required', 
                            'integer', 
                            'min:1', 
                            'max:' . $produk->stok
                        ],
            ]);
            $alamatDefault = Alamat::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unprocessable Entity. Validation failed.',
                    'errors' => $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            $pemesanan = Pemesanan::firstOrCreate(
                ['id_pelanggan'=> $pelanggan->id_pelanggan, 
                'status_pemesanan'=>'Keranjang'],
                [
                    'tanggal_pemesanan'=>now(),
                    'total_harga'=> 0, 
                    'alamat_pengiriman' => $alamatDefault ? $alamatDefault->id_alamat : null,
                ]
            );

            $detailPemesanan = DetailPemesanan::where('id_pemesanan', $pemesanan->id_pemesanan)
                ->where('id_produk_variasi', $produk->id_produk_variasi)
                ->first();

            if($detailPemesanan){
                $detailPemesanan->jumlah += $request->jumlah;
                $detailPemesanan->sub_total_produk = $detailPemesanan->jumlah * $produk->harga;
                $detailPemesanan->save();
            }else{
                $detailPemesanan= new DetailPemesanan([
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                    'id_produk_variasi'=>$produk->id_produk_variasi,
                    'jumlah'=>$request->jumlah,
                    'sub_total_produk'=>$produk->harga*$request->jumlah
                ]);
                $detailPemesanan->save();
            }
            $this->updateOrderTotal($pemesanan);

            return response()->json([
                'message' => 'Produk Berhasil Dimasukan Ke Keranjang',
                'pesanan' => $pemesanan
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Error ketika memasukan kedalam keranjang',
                'details' => $e->getMessage()
            ],500);
        }
    }

    public function UpdateItemKeranjang(Request $request, $IdDetail){
        $user = $request->user();
        $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        if (!$request->has('jumlah')) {
            return response()->json([
                'message' => 'Pastikan Jumlah Ada'
            ], 400);
        }

        $detailPemesanan = DetailPemesanan::find($IdDetail);
        if (!$detailPemesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Detail Pemesanan Terkait Produk Tidak Ditemukan'
            ], 404);
        }
        $produk = $detailPemesanan->produkVariasi;

        $validator = Validator::make($request->all(), [
            'jumlah'=>['required', 
                        'integer', 
                        'min:1', 
                        'max:' . $produk->stok
                    ],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unprocessable Entity. Validation failed.',
                'errors' => $validator->errors(),
            ], 422); // 422 Unprocessable Entity
        }

        $pemesanan = $detailPemesanan->pemesanan; 

        if($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan || $pemesanan->status_pemesanan !== 'Keranjang'){
            return response()->json([
               'message' => 'Pesanan Tidak Ditemukan/Tidak Berstatus Keranjang'
            ], 404);
        }

        if($request->jumlah==0){
            $detailPemesanan->delete();
        }else{
            $produk = $detailPemesanan->produkVariasi;
            $detailPemesanan->jumlah = $request->jumlah;
            $detailPemesanan->sub_total_produk = $produk->harga*$request->jumlah;
            $detailPemesanan->save();
        }

        $this->updateOrderTotal($pemesanan);

        return response()->json([
            'message' => 'Cart updated successfully', 
            'order' => $pemesanan   
        ]);
    }

    public function DeleteItemKeranjang(Request $request, $IdDetail)
    {
        $user = $request->user();
        $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        $detailPemesanan = DetailPemesanan::find($IdDetail);
        if (!$detailPemesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Detail Pemesanan Terkait Produk Tidak Ditemukan'
            ], 404);
        }

        $pemesanan=$detailPemesanan->pemesanan;

        if($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan || $pemesanan->status_pemesanan !== 'Keranjang'){
            return response()->json([
               'message' => 'Pesanan Tidak Ditemukan/Tidak Berstatus Keranjang'
            ], 404);
        }
        $detailPemesanan->delete();
        $this->updateOrderTotal($pemesanan);

        if($pemesanan->detailPemesanan()->count()===0){
            $pemesanan->delete();
        }

        return response()->json([
            'message' => 'Item telah dihapus dari keranjang'
        ]);
    }

    private function updateOrderTotal(Pemesanan $pemesanan)
    {
        $pemesanan->total_harga = $pemesanan->detailPemesanan->sum('sub_total_produk');
        $pemesanan->save();
    }
    
    public function getPemesananMaster()
    {
        $pemesanan = Pemesanan::with([
            'pelanggan',
            'pembayaran',
            'pengiriman',
            'detailPemesanan.produkVariasi' => function($query) {
                $query->with([
                    'gambarVariasi',
                    'produk.gambarProduk',
                    'detailProdukVariasi.opsiVariasi.tipeVariasi'
                ]);
            },
            'ulasan' => function($query) {
                $query->with(['balasan']); // Memuat relasi balasan
            },
            'penggunaanVoucher'
        ])
        ->where('status_pemesanan', '!=', 'keranjang')
        ->get();

        $formattedPemesanan = [];

        foreach ($pemesanan as $order) {
            $formattedCart = [
                'id_pemesanan' => $order->id_pemesanan,
                'id_pelanggan' => $order->id_pelanggan,
                'nama_pelanggan'=> $order->pelanggan->username,
                'tanggal_pemesanan' => $order->tanggal_pemesanan,
                'alamat_pengiriman' => $order->alamat_pengiriman,
                'total_harga' => $order->total_harga,
                'status_pemesanan' => $order->status_pemesanan,
                'detail_pemesanan' => [],
                'pembayaran' => $order->pembayaran ? [
                    'id_pembayaran' => $order->pembayaran->id_pembayaran,
                    'metode_pembayaran' => $order->pembayaran->metode_pembayaran,
                    'status_pembayaran' => $order->pembayaran->status_pembayaran,
                    'total_pembayaran' => $order->pembayaran->total_pembayaran,
                    'tanggal_pembayaran' => $order->pembayaran->waktu_pembayaran
                ] : null,
                'potongan_harga' => $order->penggunaanVoucher ? $order->penggunaanVoucher->jumlah_diskon : null,
                'pengiriman' => $order->pengiriman ? [
                    'id_pengiriman' => $order->pengiriman->id_pengiriman,
                    'kurir' => $order->pengiriman->kurir,
                    'status_pengiriman' => $order->pengiriman->status_pengiriman,
                    'biaya_pengiriman' => $order->pengiriman->biaya_pengiriman
                ] : null
            ];

            foreach ($order->detailPemesanan as $detail) {
                // Pastikan produkVariasi tidak null
                if (!$detail->produkVariasi) {
                    continue;
                }

                // Ambil gambar variasi, jika tidak ada ambil gambar produk pertama
                $gambarVariasi = null;
                if ($detail->produkVariasi->gambarVariasi && $detail->produkVariasi->gambarVariasi->count() > 0) {
                    $gambarVariasi = $detail->produkVariasi->gambarVariasi->first()->gambar;
                } elseif ($detail->produkVariasi->produk && 
                        $detail->produkVariasi->produk->gambarProduk && 
                        $detail->produkVariasi->produk->gambarProduk->count() > 0) {
                    $gambarVariasi = $detail->produkVariasi->produk->gambarProduk->first()->gambar;
                }

                // Membuat string variasi
                $variations = [];
                if ($detail->produkVariasi->detailProdukVariasi) {
                    foreach ($detail->produkVariasi->detailProdukVariasi as $produkVariasi) {
                        // Pastikan opsiVariasi dan tipeVariasi tidak null
                        if ($produkVariasi->opsiVariasi && 
                            $produkVariasi->opsiVariasi->tipeVariasi) {
                            $variations[] = $produkVariasi->opsiVariasi->tipeVariasi->nama_tipe . ': ' . $produkVariasi->opsiVariasi->nama_opsi;
                        }
                    }
                }
                $variationsString = implode(', ', $variations);

                // Pastikan produk tidak null
                $namaProduk = $detail->produkVariasi->produk ? $detail->produkVariasi->produk->nama_produk : 'Produk Tidak Dikenal';

                $formattedCart['detail_pemesanan'][] = [
                    'id_detail_pemesanan' => $detail->id_detail_pemesanan,
                    'id_produk_variasi' => $detail->id_produk_variasi,
                    'id_pemesanan' => $detail->id_pemesanan,
                    'jumlah' => $detail->jumlah,
                    'sub_total_produk' => $detail->sub_total_produk,
                    'produk_variasi' => [
                        'nama_produk' => $namaProduk,
                        'id_produk_variasi' => $detail->produkVariasi->id_produk_variasi,
                        'id_produk' => $detail->produkVariasi->id_produk,
                        'stok' => $detail->produkVariasi->stok,
                        'berat' => $detail->produkVariasi->berat,
                        'harga' => $detail->produkVariasi->harga,
                        'gambar' => $gambarVariasi,
                        'variasi' => $variationsString
                    ],
                    'ulasan' => $detail->pemesanan->ulasan
                    ->where('id_produk_variasi', $detail->id_produk_variasi)
                    ->where('id_pemesanan', $detail->id_pemesanan)
                    ->map(function ($ulasan) {
                        return [
                            'id_ulasan' => $ulasan->id_ulasan,
                            'id_rating' => $ulasan->id_rating,
                            'ulasan' => $ulasan->ulasan,
                            'balasan' => $ulasan->balasan->map(function ($balasan) {
                                return [
                                    'id_balasan' => $balasan->id_balasan,
                                    'balasan' => $balasan->balasan,
                                ];
                            }),
                        ];
                    }),
                ];
            }

            $formattedPemesanan[] = $formattedCart;
        }

        return response()->json([
            'status' => true,
            'data' => $formattedPemesanan,
        ], 200);
    }

    public function getPemesanan(Request $request)
    {
        $pelanggan = Auth::user()->pelanggan;

        // Gunakan get() untuk mengambil semua pemesanan pelanggan
        $pemesanan = Pemesanan::with([
            'detailPemesanan.produkVariasi.gambarVariasi', 
            'detailPemesanan.produkVariasi.produk.gambarProduk', 
            'detailPemesanan.produkVariasi.detailProdukVariasi.opsiVariasi.tipeVariasi', 
            'pembayaran', 
            'pengiriman',
            'penggunaanVoucher',
            'ulasan' => function($query) {
                $query->with(['balasan']); // Memuat relasi balasan
            },
        ])
        ->where('id_pelanggan', $pelanggan->id_pelanggan)
        ->where('status_pemesanan', '!=', 'keranjang')
        ->get();

        if($pemesanan->isEmpty()){
            return response()->json([
                'message'=>'Pelanggan Belum Pernah Memesan Produk'
            ], 404);
        }

        $formattedPemesanan = [];

        foreach ($pemesanan as $order) {
            $formattedCart = [
                'id_pemesanan' => $order->id_pemesanan,
                'id_pelanggan' => $order->id_pelanggan,
                'tanggal_pemesanan' => $order->tanggal_pemesanan,
                'alamat_pengiriman' => $order->alamat_pengiriman,
                'total_harga' => $order->total_harga,
                'status_pemesanan' => $order->status_pemesanan,
                'detail_pemesanan' => [],
                'pembayaran' => $order->pembayaran ? [
                    'id_pembayaran' => $order->pembayaran->id_pembayaran,
                    'metode_pembayaran' => $order->pembayaran->metode_pembayaran,
                    'status_pembayaran' => $order->pembayaran->status_pembayaran,
                    'total_pembayaran' => $order->pembayaran->total_pembayaran,
                    'snap_token'=>$order->pembayaran->snap_token,
                    'tanggal_pembayaran' => $order->pembayaran->waktu_pembayaran
                ] : null,
                'potongan_harga' => $order->penggunaanVoucher ? $order->penggunaanVoucher->jumlah_diskon : null,
                'pengiriman' => $order->pengiriman ? [
                    'id_pengiriman' => $order->pengiriman->id_pengiriman,
                    'kurir' => $order->pengiriman->kurir,
                    'status_pengiriman' => $order->pengiriman->status_pengiriman,
                    'biaya_pengiriman' => $order->pengiriman->biaya_pengiriman
                ] : null
            ];

            foreach ($order->detailPemesanan as $detail) {
                // Ambil gambar variasi, jika tidak ada ambil gambar produk pertama
                $gambarVariasi = $detail->produkVariasi->gambarVariasi->first() ? 
                    $detail->produkVariasi->gambarVariasi->first()->gambar : 
                    $detail->produkVariasi->produk->gambarProduk->first()->gambar;

                // Membuat string variasi
                $variations = [];
                foreach ($detail->produkVariasi->detailProdukVariasi as $produkVariasi) {
                    $variations[] = $produkVariasi->opsiVariasi->tipeVariasi->nama_tipe . ': ' . $produkVariasi->opsiVariasi->nama_opsi;
                }
                $variationsString = implode(', ', $variations);

                $formattedCart['detail_pemesanan'][] = [
                    'id_detail_pemesanan' => $detail->id_detail_pemesanan,
                    'id_produk_variasi' => $detail->id_produk_variasi,
                    'id_pemesanan' => $detail->id_pemesanan,
                    'jumlah' => $detail->jumlah,
                    'sub_total_produk' => $detail->sub_total_produk,
                    'produk_variasi' => [
                        'nama_produk' => $detail->produkVariasi->produk->nama_produk,
                        'id_produk_variasi' => $detail->produkVariasi->id_produk_variasi,
                        'id_produk' => $detail->produkVariasi->id_produk,
                        'stok' => $detail->produkVariasi->stok,
                        'berat' => $detail->produkVariasi->berat,
                        'harga' => $detail->produkVariasi->harga,
                        'gambar' => $gambarVariasi,
                        'variasi' => $variationsString
                    ],
                    'ulasan' => $detail->pemesanan->ulasan
                    ->where('id_produk_variasi', $detail->id_produk_variasi)
                    ->where('id_pemesanan', $detail->id_pemesanan)
                    ->map(function ($ulasan) {
                        return [
                            'id_ulasan' => $ulasan->id_ulasan,
                            'id_rating' => $ulasan->id_rating,
                            'ulasan' => $ulasan->ulasan,
                            'balasan' => $ulasan->balasan->map(function ($balasan) {
                                return [
                                    'id_balasan' => $balasan->id_balasan,
                                    'balasan' => $balasan->balasan,
                                ];
                            }),
                        ];
                    }),
                ];
            }

            $formattedPemesanan[] = $formattedCart;
        }

        return response()->json([
            'status' => true,
            'data' => $formattedPemesanan,
        ], 200);
    }
}