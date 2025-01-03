<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provinsi;
use App\Models\Kota;
use App\Models\KodePos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AlamatController extends Controller
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = 'e38f2e04465e24f524e025f12121915c';
        $this->baseUrl = 'https://api.rajaongkir.com/starter/';
    }

    public function ambilData()
    {
        try {
            DB::beginTransaction();

            $provinsi = $this->getProvinsi();
            $this->simpanDataProvinsi($provinsi);

            $kota = $this->getKota();
            $this->simpanDataKota($kota);

            DB::commit();
            return response()->json(['message' => 'Data berhasil diambil dan disimpan'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ambilData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getProvinsi()
    {
        try {
            Log::info('Attempting to fetch provinces from: ' . $this->baseUrl . 'province');
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . 'province');

            Log::info('RajaOngkir API Response: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rajaongkir']['results'])) {
                    return $data['rajaongkir']['results'];
                } else {
                    Log::error('Unexpected response structure: ' . json_encode($data));
                    throw new \Exception('Unexpected response structure from RajaOngkir API');
                }
            } else {
                Log::error('RajaOngkir API Error: ' . $response->body());
                throw new \Exception('Failed to fetch provinces from RajaOngkir API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error in getProvinsi: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getKota()
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . 'city');

            if ($response->successful()) {
                $data = $response->json();
                return $data['rajaongkir']['results'] ?? [];
            } else {
                Log::error('RajaOngkir API Error: ' . $response->body());
                throw new \Exception('Failed to fetch cities from RajaOngkir API');
            }
        } catch (\Exception $e) {
            Log::error('Error in getKota: ' . $e->getMessage());
            throw $e;
        }
    }

    private function simpanDataProvinsi($data)
    {
        foreach ($data as $provinsi) {
            Provinsi::updateOrCreate(
                ['id_provinsi' => $provinsi['province_id']],
                ['provinsi' => $provinsi['province']]
            );
        }
    }

    private function simpanDataKota($data)
    {
        foreach ($data as $kota) {
            Kota::updateOrCreate(
                ['id_kota' => $kota['city_id']],
                [
                    'id_provinsi' => $kota['province_id'],
                    'tipe_kota' => $kota['type'],
                    'nama_kota' => $kota['city_name'],
                ]
            );
            if (isset($kota['postal_code'])) {
                KodePos::updateOrCreate(
                    ['id_kota' => $kota['city_id'], 'kode_pos' => $kota['postal_code']],
                    ['id_kota' => $kota['city_id']]
                );
            }
        }
    }

    // public function getCities()
    // {
    //     try {
    //         // Panggil endpoint /city dari API RajaOngkir
    //         $response = Http::withHeaders([
    //             'key' => $this->apiKey
    //         ])->get($this->baseUrl . 'city');

    //         if ($response->successful()) {
    //             $data = $response->json();
    //             return response()->json([
    //                 'status' => 'success',
    //                 'data' => $data['rajaongkir']['results'] ?? []
    //             ], 200);
    //         } else {
    //             Log::error('RajaOngkir API Error: ' . $response->body());
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Failed to fetch cities from RajaOngkir API',
    //                 'details' => $response->body()
    //             ], $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Error in getCities: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while fetching cities',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }


   public function ambilProvinsi()
    {
        $provinsi = Provinsi::all(); // Sesuaikan dengan model Anda
        return response()->json($provinsi);
    }

    public function getKabupaten($provinsiId)
    {
        $kabupaten = Kota::where('id_provinsi', $provinsiId)->get(); // Sesuaikan dengan model Anda
        
        if ($kabupaten->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten/Kota tidak ditemukan untuk Provinsi dengan id_provinsi: ' . $provinsiId
            ], 404);
        }
        return response()->json($kabupaten);
    }

    public function getKodePos($kabupatenId)
    {
        $kodePos = KodePos::where('id_kota', $kabupatenId)->get(); 

        if ($kodePos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode pos tidak ditemukan untuk kabupaten/kota dengan id: ' . $kabupatenId
            ], 404);
        }
        return response()->json($kodePos);
    }
}