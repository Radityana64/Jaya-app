<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PembayaranViewController extends Controller
{
    /**
     * Menampilkan halaman sukses setelah pembayaran berhasil.
     */
    public function showSuccessPage($id)
    {
        $order = Pemesanan::with(['pembayaran', 'detailPemesanan.produkVariasi.produk'])
            ->findOrFail($id);

        return view('payment.success', compact('order'));
    }

    /**
     * Menampilkan halaman gagal setelah pembayaran gagal.
     */
    public function showFailedPage($id)
    {
        $order = Pemesanan::with(['pembayaran', 'detailPemesanan.produkVariasi.produk'])
            ->findOrFail($id);

        return view('payment.failed', compact('order'));
    }

    /**
     * Menampilkan halaman menunggu jika pembayaran dalam status pending.
     */
    public function showWaitingPage()
    {
        
        $pembayaranPending = Pembayaran::with('pemesanan')
            ->whereHas('pemesanan', function($query) {
                $query->where('status_pemesanan', 'Proses_Pembayaran');
            })
            ->where('status_pembayaran', 'Pending')
            ->get();
            
        return view('payment.waiting', compact('pembayaranPending'));
        // $pembayaran = Pembayaran::with('pemesanan')
            //     ->where('status_pembayaran', 'Pending')
            //     ->whereHas('pemesanan', function($query) {
            //         $query->where('status_pemesanan', 'Proses_Pembayaran');
            //     })
            //     ->first();
            
        if (!$pembayaran) {
            return redirect()->route('dashboard')
                ->with('error', 'Pembayaran tidak ditemukan atau status tidak valid');
        }
            
        // return view('payment.waiting', compact('pembayaran'));    
    }
}
