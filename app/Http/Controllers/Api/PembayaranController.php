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
use Midtrans\Config;
use Midtrans\Transaction;
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
    
    public function cancelTransaction($transactionId)
    {
        if (empty($transactionId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction ID is required'
            ], 400);
        }
        try {
            // Membatalkan transaksi berdasarkan transaction_id atau order_id
            $response = Transaction::cancel($transactionId);

            // Log untuk debugging
            \Log::info('Cancel Transaction Response:', (array) $response);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction canceled successfully',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            // Tangani error
            \Log::error('Cancel Transaction Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createPayment(Request $request)
    {
        $requiredFields = [
            'order_id', 'total_amount', 'items', 'address', 
            'firstName', 'email', 'phone'
        ];
        
        // Cari field yang hilang
        $missingFields = array_diff($requiredFields, array_keys($request->all()));
        
        // Jika ada field yang hilang, kembalikan respons 400
        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Field wajib belum diisi: ' . implode(', ', $missingFields),
            ], 400);
        }

        // Tambahkan detailed logging
        \Log::info('Received Payment Request Data:', $request->all());

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.id' => 'required',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
            'address' => 'required|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'voucher_discount' => 'nullable|numeric|min:0',
            'firstName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        // Jika validasi gagal, log detail error
        if ($validator->fails()) {
            \Log::error('Validation Errors:', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

        // Cari pesanan dalam status keranjang
        $pemesanan = Pemesanan::where('id_pemesanan', $request->input('order_id'))
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('status_pemesanan', 'Keranjang')
            ->first();

        // Cek apakah pesanan ditemukan
        if (!$pemesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan atau tidak dalam status keranjang'
            ], 404);
        }

        // Validasi item-item dalam pesanan
        $items = $request->input('items');
        $detailPemesanan = $pemesanan->detailPemesanan;
        $variasiProduk = Pemesanan::with('detailPemesanan.produkVariasi')->get();

        foreach ($items as $item) {
            // Cari detail pemesanan yang sesuai dengan item
            $matchedDetail = $detailPemesanan->first(function ($detail) use ($item) {
                return $detail->id_produk_variasi == $item['id'];
            });

            // Cek apakah produk variasi ada dalam pesanan
            if (!$matchedDetail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk variasi tidak ditemukan dalam pesanan',
                    'invalid_item_id' => $item['id']
                ], 404);
            }
        }

        foreach ($items as $item) {
            $variasi = $variasiProduk->first(function ($variasi) use ($item) {
                return $variasi->detailPemesanan->contains('id_produk_variasi', $item['id']);
            });
        
            if ($variasi) {
                $stokTersedia = $variasi->detailPemesanan->where('id_produk_variasi', $item['id'])->first()->produkVariasi->stok;
                
                if ($item['quantity'] > $stokTersedia) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Jumlah yang dipesan melebihi stok tersedia',
                        'invalid_item_id' => $item['id'],
                        'stok_tersedia' => $stokTersedia
                    ], 400);
                }
            }
        }
        

        // Ambil data dari permintaan
        $orderId = $request->input('order_id');
        $totalAmount = $request->input('total_amount');
        $items = $request->input('items');
        $address = $request->input('address');
        $shippingCost = $request->input('shipping_cost', 0);
        $voucherDiscount = $request->input('voucher_discount', 0);
        $firstName = $request->input('firstName');
        $email = $request->input('email');
        $phone = $request->input('phone');

        $transactionDetails = [
            'order_id' => 'ORDER-' . $orderId . '-' . time(),
            'gross_amount' => $totalAmount, // Pastikan dalam integer
        ];

        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => $item['id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'name' => $item['name'],
            ];
        }

        // Tambahkan biaya pengiriman dan diskon voucher sebagai item detail
        if ($shippingCost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $shippingCost,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        if ($voucherDiscount > 0) {
            $itemDetails[] = [
                'id' => 'VOUCHER_DISCOUNT',
                'price' => -$voucherDiscount, // Diskon menggunakan harga negatif
                'quantity' => 1,
                'name' => 'Diskon Voucher',
            ];
        }

        $payload = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $firstName,
                'email' => $email,
                'phone' => $phone,
                'billing_address' => [
                    'address' => $address,
                ],
                'shipping_address' => [
                    'address' => $address,
                ],
            ],
        ];


        // Debugging
        \Log::info('Midtrans Payload:', $payload);

        // Mengambil Snap token
        try {
            $snapToken = Snap::getSnapToken($payload);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            \Log::error('Midtrans Token Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate payment token',
                'message' => $e->getMessage()
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
                $payment->metode_pembayaran = $this->mapPaymentMethod($notification);
                $payment->total_pembayaran = $notification['gross_amount'];
                $payment->status_pembayaran = $this->mapTransactionStatus($transaction_status);
                
                
                if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
                    $payment->waktu_pembayaran = date('Y-m-d H:i:s', strtotime($notification['transaction_time']));
                }
                
                $payment->save();
                
                // Handle transaction status
                switch ($transaction_status) {
                    case 'capture':
                    case 'settlement':
                        $this->handleSuccessPayment($order, $notification);
                        break;
                    case 'pending':
                        $this->handlePendingPayment($order, $notification);
                        break;
                    case 'cancel':
                    case 'deny':
                    case 'expire':
                        $this->handlePaymentCancellation($order, $notification, $payment, $transaction_status);
                        break;
                }

                DB::commit();
                Log::info('Payment processed successfully', ['order_id' => $order_id, 'status' => $transaction_status]);
                
                return response()->json(['status' => 'success', 'transaction_status' => $transaction_status]);
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error processing payment:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Callback processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function mapTransactionStatus($status)
    {
        $statusMap = [
            'capture' => 'Berhasil',
            'settlement' => 'Berhasil',
            'pending' => 'Pending',
            'cancel' => 'Gagal',
            'deny' => 'Gagal',
            'expire' => 'Expired'
        ];
        return $statusMap[$status] ?? 'Pending';
    }

    private function mapPaymentMethod($notification)
    {
        $payment_type = $notification['payment_type'];
        $methodDetails = [];

        switch ($payment_type) {
            case 'gopay':
                $methodDetails = ['gopay'];
                break;
            case 'shopeepay':
                $methodDetails = ['shopeepay'];
                break;
            case 'cstore':
                $store = $notification['store'] ?? 'unknown';
                $methodDetails = ['cstore', $store];
                break;
            case 'bank_transfer':
                $bank = $notification['va_numbers'][0]['bank'] ?? ($notification['permata_va_number'] ? 'permata' : 'unknown');
                $methodDetails = ['bank_transfer', $bank];
                break;
            case 'qris':
                $acquirer = $notification['acquirer'] ?? 'unknown';
                $methodDetails = ['qris', $acquirer];
                break;
            case 'credit_card':
                $bank = $notification['bank'] ?? 'unknown';
                $methodDetails = ['credit_card', $bank];
                break;
            default:
                $methodDetails = [$payment_type];
                break;
        }

        return implode(', ', $methodDetails);
    }

    private function handleSuccessPayment($order, $notification)
    {
        Log::info('Processing successful payment', ['order_id' => $order->id_pemesanan]);
        
        $order->status_pemesanan = 'Proses_Pengiriman';
        $order->save();

        $pengiriman = Pengiriman::where('id_pemesanan', $order->id_pemesanan)->first();
        if ($pengiriman) {
            $pengiriman->status_pengiriman = 'Dikemas';
            $pengiriman->save();
        }
        
        Log::info('Success payment handled', ['order_id' => $order->id_pemesanan]);
    }

    private function handlePendingPayment($order, $notification)
    {
        Log::info('Processing pending payment', ['order_id' => $order->id_pemesanan]);
        
        if ($order->status_pemesanan !== 'Proses_Pembayaran') {
            $order->status_pemesanan = 'Proses_Pembayaran';
            $order->save();

            foreach ($order->detailPemesanan as $detail) {
                $produk = ProdukVariasi::find($detail->id_produk_variasi);
                if ($produk && $produk->stok >= $detail->jumlah) {
                    $produk->stok -= $detail->jumlah;
                    $produk->save();
                } else {
                    throw new \Exception("Stok tidak cukup untuk produk ID: {$detail->id_produk_variasi}");
                }
            }
        }
        
        Log::info('Pending payment handled', ['order_id' => $order->id_pemesanan]);
    }

    private function handlePaymentCancellation($order, $notification, $payment, $transaction_status)
    {
        Log::info('Processing payment cancellation', ['order_id' => $order->id_pemesanan]);
        
        $payment->status_pembayaran = $this->mapTransactionStatus($transaction_status);
        $payment->save();

        if ($order->status_pemesanan !== 'Pesanan_Dibatalkan') {
            $order->status_pemesanan = 'Pesanan_Dibatalkan';
            $order->save();

            foreach ($order->detailPemesanan as $detail) {
                $produk = ProdukVariasi::find($detail->id_produk_variasi);
                if ($produk) {
                    $produk->stok += $detail->jumlah;
                    $produk->save();
                }
            }
        }
        
        Log::info('Payment cancellation handled', ['order_id' => $order->id_pemesanan]);
    }

    public function storeSnapToken(Request $request)
    {
        $requiredFields = ['order_id', 'snap_token'];
        // Cari field yang hilang
        $missingFields = array_diff($requiredFields, array_keys($request->all()));
        
        // Jika ada field yang hilang, kembalikan respons 400
        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Field wajib belum diisi: ' . implode(', ', $missingFields),
            ], 400);
        }
        // Validate input
        $request->validate([
            'snap_token' => 'required|string',
        ]);

        try {
            $user = $request->user();
            $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();

            // Cari pesanan
            $pemesanan = Pemesanan::where('id_pemesanan', $request->input('order_id'))
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->whereNotIn('status_pemesanan', ['Keranjang', 'Pesanan_Diterima'])
                ->first();

            // Validasi status pemesanan
            if (!$pemesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan atau status tidak valid'
                ], 404);
            }
            // Assuming you have an order_id sent in the request
            $snap_token = $request->input('snap_token');
            $order_id = $request->input('order_id'); // Get order_id from request

            // Find or create payment entry
            $payment = Pembayaran::where('id_pemesanan', $order_id)->first();

            if (!$payment) {
                $payment = new Pembayaran();
                $payment->id_pemesanan = $order_id;
            }

            // Save Snap token
            $payment->snap_token = $snap_token;
            $payment->save();

            // Log success
            Log::info('Snap token saved successfully', ['order_id' => $order_id]);

            return response()->json(['status' => 'success', 'message' => 'Snap token saved successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error saving Snap token:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Failed to save Snap token.'], 500);
        }
    }
}
