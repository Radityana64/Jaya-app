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
        $alamat = Alamat::with(['kodePos', 'pelanggan'])
            ->where('id_pelanggan', $id)
            ->get();

        if (!$alamat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $alamat
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
        $alamat = Alamat::with(['kodePos', 'pelanggan'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $alamat
        ], 200);
    }

    // Create a new address
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pelanggan' => 'required|exists:tb_pelanggan,id_pelanggan',
            'id_kode_pos' => 'required|exists:tb_kode_pos,id_kode_pos',
            'nama_jalan' => 'required|string|max:255',
            'detail_lokasi' => 'nullable|string|max:255',
        ]);

        $address = Alamat::create($validatedData);

        return response()->json(['message' => 'Address created successfully', 'data' => $address], 201);
    }

    // Update an existing address
    public function update(Request $request, $id_alamat)
    {
        $address = Alamat::findOrFail($id_alamat);

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
        $address = Alamat::findOrFail($id_alamat);
        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }

    // Get cities by province
    public function getCitiesByProvince($id_provinsi)
    {
        $cities = Kota::where('id_provinsi', $id_provinsi)->get();
        return response()->json($cities);
    }

    // Get postal codes by city
    public function getPostalCodesByCity($id_kota)
    {
        $postalCodes = KodePos::where('id_kota', $id_kota)->get();
        return response()->json($postalCodes);
    }
}

