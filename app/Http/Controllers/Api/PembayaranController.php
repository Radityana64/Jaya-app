<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengiriman;
use App\Models\ProdukVariasi;
use App\Models\Pelanggan;
use App\Models\PenggunaanVoucher;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class PembayaranController extends Controller
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        // \Midtrans\Config::$is3ds = true; // jika Anda ingin menggunakan 3D Secure
    }    

    public function createPayment(Request $request, $id_pemesanan)
    {
        try {
            $order = Pemesanan::with([
                'detailPemesanan.produkVariasi.produk', 
                'pelanggan', 
                'pengiriman', 
                'penggunaanVoucher.voucherPelanggan.voucher'
            ])->findOrFail($id_pemesanan);

            // Cek status pemesanan
            if ($order->status_pemesanan !== 'Keranjang') {
                return response()->json([
                    'error' => 'Status pesanan tidak valid untuk pembayaran'
                ], 400);
            }

            // Persiapan items
            $items = [];
            foreach ($order->detailPemesanan as $detail) {
                $items[] = [
                    'id' => $detail->id_produk_variasi,
                    'price' => $detail->produkVariasi->harga,
                    'quantity' => $detail->jumlah,
                    'name' => $detail->produkVariasi->produk->nama_produk
                ];
            }

            // Tambahkan biaya pengiriman
            $items[] = [
                'id' => 'SHIPPING-FEE',
                'price' => $order->pengiriman->biaya_pengiriman,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman'
            ];

            // Menghitung total harga
            $total_harga = $order->total_harga;

            // Cek apakah ada penggunaan voucher
            if ($order->penggunaanVoucher->isNotEmpty()) {
                // Ambil voucher yang digunakan (asumsi hanya satu voucher yang digunakan)
                $voucher = $order->penggunaanVoucher->first()->voucherPelanggan->voucher;

                // Hitung diskon
                $diskon = ($total_harga * $voucher->diskon) / 100;
                
                // Tambahkan item diskon
                $items[] = [
                    'id' => 'VOUCHER-DISCOUNT',
                    'price' => -$diskon,
                    'quantity' => 1,
                    'name' => 'Diskon: ' . $voucher->nama_voucher
                ];

                // Kurangi total harga dengan diskon
                $total_harga -= $diskon;
            }

            // Tambahkan biaya pengiriman ke total harga
            $total_harga += $order->pengiriman->biaya_pengiriman;

            // Detail transaksi
            $transaction_details = [
                'order_id' => 'ORDER-' . $order->id_pemesanan . '-' . time(),
                'gross_amount' => $total_harga
            ];

            // Detail pelanggan
            $customer_details = [
                'first_name' => $order->pelanggan->nama_pelanggan,
                'email' => $order->pelanggan->email,
                'phone' => $order->pelanggan->telepon,
                'billing_address' => [
                    'address' => $order->alamat_pengiriman
                ]
            ];

            // Parameter Midtrans
            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'customer_details' => $customer_details,
                'item_details' => $items,
                'finish' => route('payment.success', $order->id_pemesanan),
                'error' => route('payment.failed', $order->id_pemesanan),
                'pending' => route('payment.waiting', $order->id_pemesanan)
            ];
            
            // Log parameter untuk debugging
            Log::info('Midtrans params:', ['params' => $midtrans_params]);

            // Dapatkan Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($midtrans_params);
            
            // Validasi snap token
            if (empty($snapToken)) {
                Log::error('Empty snap token received from Midtrans');
                throw new \Exception('Failed to get snap token from Midtrans');
            }

            // Buat record pembayaran
            $pembayaran = Pembayaran::create([
                'id_pemesanan' => $order->id_pemesanan,
                'snap_token' => $snapToken,
                'status_pembayaran' => 'Pending',
                'total_pembayaran' => $transaction_details['gross_amount']
            ]);

            // Perbarui status pemesanan
            $order->status_pemesanan = 'Proses_Pembayaran';
            $order->save();

            return response()->json([
                'snap_token' => $snapToken,
                'order' => $order,
                'order_id' => $order->id_pemesanan,
                'total_harga' => $total_harga
            ]);

        } catch (\Exception $e) {
            // Log error terperinci
            Log::error('Payment creation error: ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Gagal membuat pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            $notification = $request->all();
            
            // Log the notification for debugging
            Log::info('Midtrans Callback received:', $notification);

            $order_id = explode('-', $notification['order_id'])[1]; // Get the actual order ID
            $transaction_status = $notification['transaction_status'];
            $payment_type = $notification['payment_type'];
            $transaction_id = $notification['transaction_id'];
            
            // Verify signature
            $serverKey = config('midtrans.server_key');
            $orderId = $notification['order_id'];
            $statusCode = $notification['status_code'];
            $grossAmount = $notification['gross_amount'];
            $signatureKey = $notification['signature_key'];
            
            $validSignature = hash('sha512', 
                $orderId . 
                $statusCode . 
                $grossAmount . 
                $serverKey
            );

            if ($signatureKey !== $validSignature) {
                Log::error('Invalid signature', [
                    'received' => $signatureKey,
                    'calculated' => $validSignature
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            DB::beginTransaction();
            try {
                // Find the order
                $order = Pemesanan::findOrFail($order_id);
                
                // Find or create payment record
                $payment = Pembayaran::where('id_pemesanan', $order_id)->first();
                
                if (!$payment) {
                    $payment = new Pembayaran();
                    $payment->id_pemesanan = $order_id;
                }
                
                // Update payment details
                $payment->id_transaksi_midtrans = $transaction_id;
                $payment->metode_pembayaran = $payment_type;
                $payment->total_pembayaran = $notification['gross_amount'];
                $payment->status_pembayaran = $this->mapTransactionStatus($transaction_status);
                
                if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
                    $payment->waktu_pembayaran = date('Y-m-d H:i:s', strtotime($notification['transaction_time']));
                }
                
                $payment->save();
                
                // Update order status based on transaction status
                switch ($transaction_status) {
                    case 'capture':
                    case 'settlement':
                        $this->handleSuccessPayment($order, $notification);
                        break;
                    
                    case 'pending':
                        $this->handlePendingPayment($order, $notification);
                        break;
                    
                    case 'deny':
                    case 'expire':
                    case 'cancel':
                        $this->handleFailedPayment($order, $notification, $transaction_status);
                        break;
                }
                
                DB::commit();
                
                // Log successful processing
                Log::info('Payment processed successfully', [
                    'order_id' => $order_id,
                    'status' => $transaction_status
                ]);
                
                return response()->json(['status' => 'success']);
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error processing payment:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Callback processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function mapTransactionStatus($status)
    {
        switch ($status) {
            case 'capture':
            case 'settlement':
                return 'Berhasil';
            case 'pending':
                return 'Pending';
            case 'deny':
                return 'Ditolak';
            case 'expire':
                return 'Kadaluarsa';
            case 'cancel':
                return 'Dibatalkan';
            default:
                return 'Pending';
        }
    }

    private function handleSuccessPayment($order, $notification)
    {
        Log::info('Processing successful payment', ['order_id' => $order->id_pemesanan]);
        
        $order->status_pemesanan = 'Dibayar';
        $order->save();

        // Update pengiriman status
        $pengiriman = Pengiriman::where('id_pemesanan', $order->id_pemesanan)->first();
        if ($pengiriman) {
            $pengiriman->status_pengiriman = 'Dikemas';
            $pengiriman->save();
        }

        // Update product stock
        foreach ($order->detailPemesanan as $detail) {
            $produk = ProdukVariasi::find($detail->id_produk_variasi);
            if ($produk) {
                $produk->stok -= $detail->jumlah;
                $produk->save();
            }
        }
        
        Log::info('Success payment handled', ['order_id' => $order->id_pemesanan]);
    }

    private function handlePendingPayment($order, $notification)
    {
        Log::info('Processing pending payment', ['order_id' => $order->id_pemesanan]);
        
        $order->status_pemesanan = 'Proses_Pembayaran';
        $order->save();
        
        Log::info('Pending payment handled', ['order_id' => $order->id_pemesanan]);
    }

    private function handleFailedPayment($order, $notification, $status)
    {
        Log::info('Processing failed payment', [
            'order_id' => $order->id_pemesanan,
            'status' => $status
        ]);
        
        $order->status_pemesanan = 'Gagal';
        $order->save();
        
        Log::info('Failed payment handled', ['order_id' => $order->id_pemesanan]);
    }

    public function getSnapToken($id_pemesanan)
    {
        $pembayaran = Pembayaran::with('pemesanan')
            ->where('id_pemesanan', $id_pemesanan)
            ->where('status_pembayaran', 'Pending')
            ->whereHas('pemesanan', function($query) {
                $query->where('status_pemesanan', 'Proses_Pembayaran');
            })
            ->first();

        if (!$pembayaran) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran tidak ditemukan atau status tidak valid'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'snap_token' => $pembayaran->snap_token
            ]
        ]);
    }
}
