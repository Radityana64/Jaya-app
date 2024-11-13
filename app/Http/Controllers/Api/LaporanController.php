<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\DetailPemesanan;
use App\Models\ProdukVariasi;
use App\Models\Kategori1;
use App\Models\Kategori2;
use App\Models\DetailProdukVariasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    public function getLaporanPenjualan(Request $request)
    {
        // Validasi input tanggal
        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Mendapatkan semua pembayaran yang berhasil dalam periode
            $pembayaran = Pembayaran::whereBetween('waktu_pembayaran', [
                    $request->tanggal_mulai . ' 00:00:00',
                    $request->tanggal_akhir . ' 23:59:59'
                ])
                ->where('status_pembayaran', 'berhasil')
                ->get();

            // Hitung total penjualan
            $total_penjualan = $pembayaran->sum('total_pembayaran');
            
            // Ambil detail pemesanan untuk pembayaran yang berhasil
            $detail_produk = $this->getDetailProduk($pembayaran->pluck('id_pemesanan'));

            // Proses dan kelompokkan data
            $result = $this->prosesDataLaporan($detail_produk);

            // Hitung total laba
            $total_laba = $this->hitungTotalLaba($result);

            return response()->json([
                'status' => true,
                'message' => 'Data laporan penjualan berhasil diambil',
                'data' => [
                    'periode' => [
                        'mulai' => $request->tanggal_mulai,
                        'akhir' => $request->tanggal_akhir
                    ],
                    'ringkasan' => [
                        'jumlah_transaksi' => $pembayaran->count(),
                        'total_penjualan' => $total_penjualan,
                        'total_laba' => $total_laba
                    ],
                    'detail' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getDetailProduk($pemesanan_ids)
    {
        return DetailPemesanan::whereIn('id_pemesanan', $pemesanan_ids)
            ->select(
                'tb_detail_pemesanan.id_pemesanan',
                'tb_detail_pemesanan.id_produk_variasi',
                'tb_produk_variasi.id_produk',
                'tb_produk.id_kategori_2',
                'tb_kategori_2.id_kategori_1',
                'tb_produk.nama_produk',
                DB::raw('SUM(tb_detail_pemesanan.jumlah) as jumlah_terjual'),
                DB::raw('SUM(tb_detail_pemesanan.sub_total_produk) as total_pendapatan')
            )
            ->join('tb_produk_variasi', 'tb_detail_pemesanan.id_produk_variasi', '=', 'tb_produk_variasi.id_produk_variasi')
            ->join('tb_produk', 'tb_produk_variasi.id_produk', '=', 'tb_produk.id_produk')
            ->join('tb_kategori_2', 'tb_produk.id_kategori_2', '=', 'tb_kategori_2.id_kategori_2')
            ->groupBy(
                'tb_detail_pemesanan.id_pemesanan',
                'tb_detail_pemesanan.id_produk_variasi', 
                'tb_produk_variasi.id_produk', 
                'tb_produk.id_kategori_2',
                'tb_kategori_2.id_kategori_1',
                'tb_produk.nama_produk'
            )
            ->get();
    }

    private function prosesDataLaporan($detail_produk)
    {
        $result = [];
        $kategori1_group = $detail_produk->groupBy('id_kategori_1');

        foreach ($kategori1_group as $id_kategori_1 => $kategori1_items) {
            $kategori1Data = $this->inisialisasiKategori1($id_kategori_1);
            $kategori2_group = $kategori1_items->groupBy('id_kategori_2');

            foreach ($kategori2_group as $id_kategori_2 => $kategori2_items) {
                $kategori2Data = $this->inisialisasiKategori2($id_kategori_2);
                $produk_group = $kategori2_items->groupBy('id_produk');

                foreach ($produk_group as $id_produk => $produk_items) {
                    $produkData = $this->prosesProduk($id_produk, $produk_items);

                    $kategori2Data['produk'][] = $produkData;
                    $this->updateDataKategori($kategori2Data, $produkData);
                }

                $kategori1Data['kategori2'][] = $kategori2Data;
                $this->updateDataKategori($kategori1Data, $kategori2Data);
            }

            $result[] = $kategori1Data;
        }

        return $result;
    }

    private function inisialisasiKategori1($id_kategori_1)
    {
        return [
            'id_kategori_1' => $id_kategori_1,
            'nama_kategori_1' => Kategori1::find($id_kategori_1)->nama_kategori,
            'jumlah_terjual' => 0,
            'total_pendapatan' => 0,
            'laba' => 0,
            'kategori2' => []
        ];
    }

    private function inisialisasiKategori2($id_kategori_2)
    {
        return [
            'id_kategori_2' => $id_kategori_2,
            'nama_kategori_2' => Kategori2::find($id_kategori_2)->nama_kategori,
            'jumlah_terjual' => 0,
            'total_pendapatan' => 0,
            'laba' => 0,
            'produk' => []
        ];
    }

    private function prosesProduk($id_produk, $produk_items)
    {
        $produkData = [
            'id_produk' => $id_produk,
            'nama_produk' => $produk_items->first()->nama_produk,
            'jumlah_terjual' => $produk_items->sum('jumlah_terjual'),
            'total_pendapatan' => $produk_items->sum('total_pendapatan'),
            'laba' => 0,
            'produk_variasi' => []
        ];

        foreach ($produk_items as $item) {
            $avg_hpp = ProdukVariasi::where('id_produk_variasi', $item->id_produk_variasi)
                ->value('hpp') ?? 0;

            $laba_variasi = $item->total_pendapatan - ($avg_hpp * $item->jumlah_terjual);

            $variasi_detail = [
                'id_produk_variasi' => $item->id_produk_variasi,
                'jumlah_terjual' => $item->jumlah_terjual,
                'total_pendapatan' => $item->total_pendapatan,
                'laba' => $laba_variasi,
                'detail_variasi' => $this->getVariasiDetail($item->id_produk_variasi)
            ];

            $produkData['produk_variasi'][] = $variasi_detail;
            $produkData['laba'] += $laba_variasi;
        }

        return $produkData;
    }

    private function updateDataKategori(&$kategoriData, $produkData)
    {
        $kategoriData['jumlah_terjual'] += $produkData['jumlah_terjual'];
        $kategoriData['total_pendapatan'] += $produkData['total_pendapatan'];
        $kategoriData['laba'] += $produkData['laba'];
    }

    private function hitungTotalLaba($result)
    {
        return collect($result)->sum(function($kategori1) {
            return collect($kategori1['kategori2'])->sum('laba');
        });
    }

    private function getVariasiDetail($id_produk_variasi)
    {
        $detail_variasi = DetailProdukVariasi::where('id_produk_variasi', $id_produk_variasi)
            ->with(['opsiVariasi.tipeVariasi'])
            ->get()
            ->map(function($detail) {
                return [
                    'tipe_variasi' => $detail->opsiVariasi->tipeVariasi->nama_tipe,
                    'opsi_variasi' => $detail->opsiVariasi->nama_opsi
                ];
            });

        return $detail_variasi;
    }
}