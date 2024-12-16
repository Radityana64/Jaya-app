<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\Provinsi;
use App\Models\Kota;
use App\Models\KodePos;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlamatDetailController extends Controller
{
    /**
     * Mengambil alamat berdasarkan ID
     */
    public function getAlamatByIdPelanggan($id)
    {
        // Mencari pelanggan berdasarkan ID
        $pelanggan = Pelanggan::with(['alamat.kodePos'])
            ->find($id); // Menggunakan find() untuk mendapatkan pelanggan

        // Jika pelanggan tidak ditemukan, kembalikan 404
        if (!$pelanggan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        }

        // Jika pelanggan ditemukan tetapi tidak memiliki alamat
        if ($pelanggan->alamat->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pelanggan tidak memiliki alamat'
            ], 404);
        }

        // Jika pelanggan ditemukan dan memiliki alamat
        return response()->json([
            'status' => 'success',
            'data' => $pelanggan->alamat
        ], 200);
    }

    /**
     * Mengambil alamat berdasarkan pelanggan yang login
     */
    public function getAlamatByPelanggan()
    {
        // Ambil ID pelanggan dari pengguna yang sedang login
        $pelanggan = Auth::user()->pelanggan; // Pastikan Anda memiliki relasi di model User

        if (!$pelanggan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        }

        // Ambil alamat berdasarkan ID pelanggan
        $alamat = Alamat::with(['kodePos.kota.provinsi'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->get();
        
        // Periksa apakah alamat kosong
        if ($alamat->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan'
            ], 404);
        }

        $formatAlamat = $alamat->map(function ($item) {
            return [
                'id_alamat' => $item->id_alamat,
                'id_pelanggan' => $item->id_pelanggan,
                'nama_jalan' => $item->nama_jalan,
                'detail_lokasi' => $item->detail_lokasi,
                'kode_pos' => [
                    'id_kode_pos' => $item->kodePos->id_kode_pos,
                    'nama_kota' => $item->kodePos->kota->nama_kota ?? '', // Ensure to handle null
                    'nama_provinsi' => $item->kodePos->kota->provinsi->provinsi ?? '', // Ensure to handle null
                    'kode_pos' => $item->kodePos->kode_pos,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formatAlamat
        ], 200);
    }

    public function getAlamatByIdAlamat(Request $request, $id_alamat)
    {
        $pelanggan = Auth::user()->pelanggan;

        // Mencari alamat berdasarkan ID
        $alamat = Alamat::with(['kodePos.kota.provinsi', 'pelanggan'])
            ->find($id_alamat); // Menggunakan find() untuk mendapatkan alamat

        // Jika alamat tidak ditemukan, kembalikan 404
        if (!$alamat) {
            return response()->json([
                'status' => false,
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        // Jika alamat ditemukan tetapi bukan milik pelanggan yang sedang login, kembalikan 403
        if ($alamat->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki izin untuk mengakses alamat ini.'
            ], 403);
        }

        // Jika alamat ditemukan dan milik pelanggan yang sedang login
        return response()->json([
            'status' => true,
            'data' => $alamat
        ]);
    }

    // Create a new address
    public function store(Request $request)
    {
        $pelanggan = Auth::user()->pelanggan;

        $requiredFields = ['id_kode_pos', 'nama_jalan', 'detail_lokasi'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }

        $kodepos = KodePos::find($request->id_kode_pos, 'id_kode_pos');

        if(!$kodepos){
            return response()->json([
                'status' => false,
                'message' => 'kode pos tidak ditemukan'
            ], 404);
        }

        $validatedData = $request->validate([
            'id_kode_pos' => 'required',
            'nama_jalan' => 'required|string|max:255',
            'detail_lokasi' => 'nullable|string|max:255',
        ]);

        $validatedData['id_pelanggan'] = $pelanggan->id_pelanggan;

        $address = Alamat::create($validatedData);

        return response()->json(['message' => 'Address created successfully', 'data' => $address], 201);
    }

    // Update an existing address
    public function update(Request $request, $id_alamat)
    {
        $address = Alamat::find($id_alamat);
        if(!$address){
            return response()->json([
                'status' => false,
                'message' => 'param salah, alamat tidak ditemukan'
            ], 404);
        }

        $pelanggan = Auth::user()->pelanggan;
        if ($address->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit alamat ini.'
            ], 403); // 403 Forbidden
        }
    
        if ($request->isNotFilled(['id_kode_pos', 'nama_jalan', 'detail_lokasi'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. No data provided.',
            ], 400); // 400 Bad Request
        }

        $kodepos = KodePos::find($request->id_kode_pos, 'id_kode_pos');

        if(!$kodepos){
            return response()->json([
                'status' => false,
                'message' => 'kode pos tidak ditemukan'
            ], 404);
        }

        $validatedData = $request->validate([
            'id_kode_pos' => 'sometimes|required|exists:tb_kode_pos,id_kode_pos',
            'nama_jalan' => 'sometimes|required|string|max:255',
            'detail_lokasi' => 'nullable|string|max:255',
        ]);

        $address->update($validatedData);

        return response()->json(['message' => 'Address updated successfully', 'data' => $address]);
    }

    // Delete an address
    public function destroy($id_alamat)
    {
        // Mencari alamat berdasarkan ID
        $address = Alamat::find($id_alamat);
        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Param salah, alamat tidak ditemukan'
            ], 404); // 404 Not Found
        }

        // Ambil pelanggan dari pengguna yang sedang login
        $pelanggan = Auth::user()->pelanggan;

        // Periksa apakah alamat milik pelanggan yang sedang login
        if ($address->id_pelanggan !== $pelanggan->id_pelanggan) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus alamat ini.'
            ], 403); // 403 Forbidden
        }

        try {
            // Hapus alamat
            $address->delete();

            return response()->json(['message' => 'Address deleted successfully']);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan saat menghapus alamat, kembalikan 500
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menghapus alamat. Silakan coba lagi nanti.'
            ], 500); // 500 Internal Server Error
        }
    }
}

