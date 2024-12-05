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
            ->firstOrFail();

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
            $user = $request->user();
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

            $validated = $request->validate([
                'id_produk_variasi'=>'required|exists:tb_produk_variasi,id_produk_variasi',
                'jumlah'=>'required|integer|min:1',
            ]);
            $alamatDefault = Alamat::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            // if ($alamatDefault) {
            //     $alamatLengkap = $this->buatAlamatLengkap($alamatDefault);
            // } else {
            //     $alamatLengkap = null;
            // }
            $pemesanan = Pemesanan::firstOrCreate(
                ['id_pelanggan'=> $pelanggan->id_pelanggan, 
                'status_pemesanan'=>'Keranjang'],
                [
                    'tanggal_pemesanan'=>now(),
                    'total_harga'=> 0, 
                    'alamat_pengiriman' => $alamatDefault ? $alamatDefault->id_alamat : null,
                ]
            );

            $produk = ProdukVariasi::findOrFail($validated['id_produk_variasi']);
            $detailPemesanan = DetailPemesanan::where('id_pemesanan', $pemesanan->id_pemesanan)
                ->where('id_produk_variasi', $produk->id_produk_variasi)
                ->first();

            if($detailPemesanan){
                $detailPemesanan->jumlah += $validated['jumlah'];
                $detailPemesanan->sub_total_produk = $detailPemesanan->jumlah * $produk->harga;
                $detailPemesanan->save();
            }else{
                $detailPemesanan= new DetailPemesanan([
                    'id_pemesanan' => $pemesanan->id_pemesanan,
                    'id_produk_variasi'=>$produk->id_produk_variasi,
                    'jumlah'=>$validated['jumlah'],
                    'sub_total_produk'=>$produk->harga*$validated['jumlah']
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
    // private function buatAlamatLengkap(Alamat $alamat)
    // {
    //     $kodePos = $alamat->kodePos;
    //     $kota = $kodePos->kota;
    //     $provinsi = $kota->provinsi;

    //     return "{$alamat->nama_jalan}, {$alamat->detail_lokasi}, {$kota->nama_kota}, {$provinsi->provinsi}, {$kodePos->kode_pos}";
    // }

    public function UpdateItemKeranjang(Request $request, $IdDetail){
        $user = $request->user();
        $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        $validated = $request->validate([
            'jumlah'=>'required|integer|min:0',
        ]);

        $detailPemesanan = DetailPemesanan::findOrFail($IdDetail);
        $pemesanan = $detailPemesanan->pemesanan; 

        if($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan || $pemesanan->status_pemesanan !== 'Keranjang'){
            return response()->json([
               'message' => 'Unauthorized'
            ], 403);
        }

        if($validated['jumlah']==0){
            $detailPemesanan->delete();
        }else{
            $produk = $detailPemesanan->produkVariasi;
            $detailPemesanan->jumlah = $validated['jumlah'];
            $detailPemesanan->sub_total_produk = $produk->harga*$validated['jumlah'];
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

        $detailPemesanan = DetailPemesanan::findOrFail($IdDetail);
        $pemesanan=$detailPemesanan->pemesanan;

        if($pemesanan->id_pelanggan !== $pelanggan->id_pelanggan || $pemesanan->status_pemesanan !== 'Keranjang'){
            return response()->json([
                'message' => 'Unauthorized'
            ]);
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
    
    // public function getPemesanan(Request $request)
    // {
    //     $pelanggan = Auth::user()->pelanggan;

    //     $pemesanan = Pemesanan::with('pembayaran', 'pengiriman','detailPemesanan.produkVariasi.gambarVariasi', 'detailPemesanan.produkVariasi.produk.gambarProduk', 
    //     'detailPemesanan.produkVariasi.detailProdukVariasi.opsiVariasi.tipeVariasi')
    //     ->where('id_pelanggan', $pelanggan->id_pelanggan)
    //     ->firstOrFail();

    //     return response()->json([
    //         'status'=>true,
    //         'data'=>$pemesanan,
    //     ], 200);
    // }
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
            'ulasan',
        ])
        ->where('id_pelanggan', $pelanggan->id_pelanggan)
        ->where('status_pemesanan', '!=', 'keranjang')
        ->get(); // Ubah dari firstOrFail() ke get()

        $formattedPemesanan = [];

        foreach ($pemesanan as $order) {
            $formattedCart = [
                'id_pemesanan' => $order->id_pemesanan,
                'id_pelanggan' => $order->id_pelanggan,
                'tanggal_pemesanan' => $order->tanggal_pemesanan,
                'alamat_pengiriman' => $order->alamat_pengiriman,
                'total_harga' => $order->total_harga,
                'status_pemesanan' => $order->status_pemesanan,
                'ulasan' => $order->ulasan ? $order->ulasan : null,
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
                    ]
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