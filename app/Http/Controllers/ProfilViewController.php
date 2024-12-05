<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ProfilViewController extends Controller
{
    public function index()
    {
        // $user = Auth::user();
        return view('pelanggan.app');
    }

    public function loadPage($page)
    {
        $validPages = ['profil', 'alamat', 'voucher', 'pesanan'];
        abort_if(!in_array($page, $validPages), 404);

        if (request()->ajax()) {
            return view("pelanggan.{$page}"); // Return the specific page view
        }

        return view('pelanggan.app', [
            'activePage' => $page // Set the active page for the sidebar
        ]);
    }
    public function indexPelanggan()
    {
        return view('pelanggan.pesanan');
    }

    public function getPesananByStatus(Request $request, $status = 'all')
{
    $token = session('auth.token');

    try {
        $client = new Client();
        $response = $client->request('GET', 'http://127.0.0.1:8000/api/pemesanan/data', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $pesananData = json_decode($response->getBody(), true);

        if ($statusCode == 200 && isset($pesananData['data'])) {
            $pesananData = $pesananData['data'];

            // Filter berdasarkan status
            $filteredPesanan = $this->filterPesananByStatus($pesananData, $status);

            // Jika request adalah AJAX, kembalikan view parsial
            if ($request->ajax()) {
                return view('pelanggan.pesanan-list', [
                    'pesanan' => $filteredPesanan
                ]);
            }

            // Untuk request biasa
            return view('pelanggan.pesanan', [
                'pesanan' => $filteredPesanan
            ]);
        }

        // Tangani jika data tidak ditemukan
        return view('pelanggan.pesanan', [
            'pesanan' => []
        ])->with('error', 'Tidak dapat mengambil data pesanan');

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Tangani error dari Guzzle
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 500;
        $errorMessage = $response ? json_decode($response->getBody(), true) : 'Terjadi kesalahan';

        // Log error
        \Log::error('Pesanan Fetch Error', [
            'status' => $statusCode,
            'message' => $errorMessage
        ]);

        // Redirect atau kembalikan error
        return redirect()->back()->with('error', 'Gagal mengambil data pesanan');
    } catch (\Exception $e) {
        // Tangani error umum
        \Log::error('Pesanan Unexpected Error', [
            'message' => $e->getMessage()
        ]);

        return redirect()->back()->with('error', 'Terjadi kesalahan tidak terduga');
    }
}

    private function filterPesananByStatus($pesananData, $status)
    {
        switch ($status) {
            case 'belum-bayar':
                return array_filter($pesananData, function($pesanan) {
                    return $pesanan['status_pemesanan'] == 'Proses_Pembayaran';
                });
            case 'dikemas':
                return array_filter($pesananData, function($pesanan) {
                    return $pesanan['status_pemesanan'] == 'Pesanan_Diterima';
                });
            case 'dikirim':
                return array_filter($pesananData, function($pesanan) {
                    return $pesanan['pengiriman']['status_pengiriman'] == 'Dikirim';
                });
            case 'selesai':
                return array_filter($pesananData, function($pesanan) {
                    return $pesanan['pengiriman']['status_pengiriman'] == 'Diterima';
                });
            default:
                return $pesananData;
        }
    }

}
