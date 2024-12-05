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
        $alamat = Pelanggan::with(['alamat.kodePos', 'alamat'])
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
        $alamat = Alamat::with(['kodePos.kota.provinsi'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->get();

        $formatAlamat = $alamat->map(function ($item){
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
        $alamat = Alamat::with(['kodePos.kota.provinsi'])
        ->findOrFail($id_alamat);
        // ->where('id_alamat', $id_alamat)
        // ->get();

        // $formatAlamat = $alamat->map(function($data){
        //     return[
        //         'id_alamat'=> $data->id_alamat,
        //         'id_pelanggan'=> $data->id_alamat, 
        //         'nama_jalan' => $data->nama_jalan,
        //         'detail_lokasi' => $data->detail_lokasi,
        //         'kode_pos' => [
        //             'id_kode_pos' => $data->kodePos->id_kode_pos,
        //             'nama_kota' => $data->kodePos->kota->nama_kota ?? '', // Ensure to handle null
        //             'nama_provinsi' => $data->kodePos->kota->provinsi->provinsi ?? '', // Ensure to handle null
        //             'kode_pos' => $data->kodePos->kode_pos,
        //         ],
        //     ];
        // });
        return response()->json([
            'status'=>true,
            'data'=>$alamat
        ]);

    }

    // Create a new address
    public function store(Request $request)
    {
        $pelanggan = Auth::user()->pelanggan;

        $validatedData = $request->validate([
            'id_kode_pos' => 'required|exists:tb_kode_pos,id_kode_pos',
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

