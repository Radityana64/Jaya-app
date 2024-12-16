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
                
                return response()->json([
                    'status' => 'success', 
                    'transaction_status' => $transaction_status
                ]);
                
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

    // public function getSnapToken($id_pemesanan)
    // {
    //     $pembayaran = Pembayaran::with('pemesanan')
    //         ->where('id_pemesanan', $id_pemesanan)
    //         ->where('status_pembayaran', 'Pending')
    //         ->whereHas('pemesanan', function($query) {
    //             $query->where('status_pemesanan', 'Proses_Pembayaran');
    //         })
    //         ->first();

    //     if (!$pembayaran) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Pembayaran tidak ditemukan atau status tidak valid'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => [
    //             'snap_token' => $pembayaran->snap_token
    //         ]
    //     ]);
    // }
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
