<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ulasan;
use App\Models\Rating;
use App\Models\Balasan;
use App\Models\ProdukVariasi;
use App\Models\Pelanggan;
use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UlasanController extends Controller
{
    public function storeUlasan(Request $request)
    {
        // Ambil user yang sedang login
        $user = auth()->user();
        $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        $requiredFields = ['id_pemesanan', 'id_produk_variasi', 'rating', 'ulasan'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));
        // Jika ada field yang hilang, kembalikan error 400
        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Field wajib belum diisi: ' . implode(', ', $missingFields)
            ], 400);
        }

        $validator = Validator::make($request->all(),[
            'id_pemesanan' => 'required',
            'id_produk_variasi' => 'required',
            'rating' => 'required|integer|between:1,5',
            'ulasan' => 'required|string|max:1000',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Cari pemesanan
        $pemesanan = Pemesanan::where('id_pemesanan', $request->id_pemesanan)
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->first();

        if(!$pemesanan) {
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        if($pemesanan->status_pemesanan !== 'Pesanan_Diterima'){
            return response()->json([
                'status' => false,
                'message' => 'Pesanan belum diterima'
            ], 403);
        }

        // Verifikasi bahwa produk variasi yang direview sesuai dengan yang dipesan
        $detailPemesanan = DetailPemesanan::where('id_pemesanan', $request->id_pemesanan)
            ->where('id_produk_variasi', $request->id_produk_variasi)
            ->first();

        if(!$detailPemesanan) {
            return response()->json([
                'status' => false,
                'message' => 'Produk variasi ini tidak ada dalam pesanan'
            ], 404);
        }

        $existingReview = Ulasan::where('id_pemesanan', $request->id_pemesanan)
            ->where('id_produk_variasi', $request->id_produk_variasi)
            ->first();
        
        if($existingReview){
            return response()->json([
                'status' => 'conflict',
                'message' => 'Anda sudah memberikan ulasan untuk variasi produk ini'
            ], 409);
        }

        $rating = Rating::where('rating', $request->rating)->first();

        $ulasan = Ulasan::create([
            'id_rating' => $rating->id_rating,
            'id_produk_variasi' => $request->id_produk_variasi,
            'id_pemesanan' => $request->id_pemesanan,
            'ulasan' => $request->ulasan
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Ulasan berhasil disimpan',
            'data' => $ulasan->load(['rating', 'pemesanan'])
        ], 201);
    }

    public function getUlasanProduk($id_produk)
    {
        $produk = DB::table('tb_produk')
            ->select('id_produk', 'nama_produk')
            ->where('id_produk', $id_produk)
            ->first();

        if (!$produk) {
            return response()->json([
                'status' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $ulasan = DB::table('tb_ulasan as u')
            ->select([
                'u.id_ulasan',
                'r.rating',
                'u.ulasan',
                'u.tanggal_dibuat',
                'pl.username as nama_pelanggan',
                'pv.id_produk_variasi',
                'pv.harga',
                'pv.stok',
                'b.id_balasan',
                'b.balasan',
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(tv.nama_tipe, ": ", ov.nama_opsi) SEPARATOR ", ") as variasi_info')
            ])
            ->join('tb_produk_variasi as pv', 'u.id_produk_variasi', '=', 'pv.id_produk_variasi')
            ->join('tb_rating as r', 'u.id_rating', '=', 'r.id_rating')
            ->join('tb_pemesanan as pm', 'u.id_pemesanan', '=', 'pm.id_pemesanan')
            ->join('tb_pelanggan as pl', 'pm.id_pelanggan', '=', 'pl.id_pelanggan')
            ->leftJoin('tb_balasan as b', 'u.id_ulasan', '=', 'b.id_ulasan')
            ->leftJoin('tb_detail_produk_variasi as dpv', 'pv.id_produk_variasi', '=', 'dpv.id_produk_variasi')
            ->leftJoin('tb_opsi_variasi as ov', 'dpv.id_opsi_variasi', '=', 'ov.id_opsi_variasi')
            ->leftJoin('tb_tipe_variasi as tv', 'ov.id_tipe_variasi', '=', 'tv.id_tipe_variasi')
            ->where('pv.id_produk', $id_produk)
            ->groupBy([
                'u.id_ulasan', 'r.rating', 'u.ulasan', 'u.tanggal_dibuat',
                'pl.username', 'pv.id_produk_variasi', 'pv.harga', 'pv.stok',
                'b.id_balasan', 'b.balasan'
            ])
            ->orderBy('u.tanggal_dibuat', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id_ulasan' => $item->id_ulasan,
                    'rating' => $item->rating,
                    'ulasan' => $item->ulasan,
                    'tanggal_dibuat' => $item->tanggal_dibuat,
                    'nama_pelanggan' => $item->nama_pelanggan,
                    'variasi' => $item->variasi_info,
                    'produk_variasi' => [
                        'id_produk_variasi' => $item->id_produk_variasi,
                        'harga' => $item->harga,
                        'stok' => $item->stok
                    ],
                    'balasan' => $item->id_balasan ? [
                        'id_balasan' => $item->id_balasan,
                        'balasan' => $item->balasan,                        
                    ] : null
                ];
            });

        $ratingStats = $this->calculateRatingStats($id_produk);

        if ($ulasan->isEmpty()) {
            return response()->json([
                "status" => true,
                "data" => [
                    "id_produk" => $produk->id_produk,
                    "nama_produk" => $produk->nama_produk,
                    'rating_summary' => $ratingStats,
                    "ringkasan_ulasan" => []
                ],
                "message" => 'Produk belum memiliki ulasan'
            ], 200);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'rating_summary' => $ratingStats,
                'ringkasan_ulasan' => $ulasan
            ]
        ]);
    }
    private function calculateRatingStats($id_produk)
    {
        // Menggunakan Query Builder untuk menghitung rating berdasarkan produk
        $ratings = DB::table('tb_ulasan as u')
            ->join('tb_rating as r', 'u.id_rating', '=', 'r.id_rating')
            ->join('tb_produk_variasi as pv', 'u.id_produk_variasi', '=', 'pv.id_produk_variasi')
            ->where('pv.id_produk', $id_produk)
            ->select('r.rating')
            ->get();

        if($ratings->isEmpty()){
            return [
                'average_rating' => 0,
                'total_reviews' => 0,
                'rating_breakdown' => [
                    5 => ['count' => 0, 'percentage' => 0],
                    4 => ['count' => 0, 'percentage' => 0],
                    3 => ['count' => 0, 'percentage' => 0],
                    2 => ['count' => 0, 'percentage' => 0],
                    1 => ['count' => 0, 'percentage' => 0]
                ]
            ];
        }

        $averageRating = $ratings->avg('rating');
        $totalReviews = $ratings->count();

        $breakdown = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];

        foreach($ratings as $rating){
            $breakdown[$rating->rating]++;
        }

        foreach ($breakdown as $rating => $count) {
            $breakdown[$rating] = [
                'count' => $count,
                'percentage' => ($totalReviews > 0) ? ($count / $totalReviews) * 100 : 0
            ];
        }

        return [
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'rating_breakdown' => $breakdown
        ];
    }

    public function SimpanBalasan(Request $request, $id_ulasan)
    {
        $requiredFields = ['balasan'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));
        // Jika ada field yang hilang, kembalikan error 400
        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Field wajib belum diisi: ' . implode(', ', $missingFields)
            ], 400);
        }

        $ulasan = Ulasan::find($id_ulasan);
        if(!$ulasan){
            return response()->json([
                'status' => false,
                'message' => 'Ulasan tidak ditemukan'
            ], 404);
        }

        $existingBalasan = Balasan::where('id_ulasan', $id_ulasan)->first();
        if ($existingBalasan) {
            return response()->json([
                'status' => false,
                'message' => 'Ulasan sudah memiliki balasan'
            ], 409); // Conflict
        }

        $validator = Validator::make($request->all(),[
            'balasan'=>'required|string|max:1000'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $balasan = $ulasan->balasan()->create([
            'balasan'=>$request->balasan
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Balasan berhasil disimpan',
            'data' => $balasan
        ], 201);
    }
}
