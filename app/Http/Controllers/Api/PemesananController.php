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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

class PemesananController extends Controller
{
    public function GetKeranjang(Request $request)
    {
        // $user = $request->user();
        // $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        $pelanggan = Auth::user()->pelanggan;

        $pemesanan = Pemesanan::with('detailPemesanan.produkVariasi')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('status_pemesanan', 'Keranjang')
            ->firstOrFail();
        return response()->json(['cart'=> $pemesanan]); 
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
}