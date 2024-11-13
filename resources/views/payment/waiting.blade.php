<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran Pending</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-center mb-6">Pembayaran Pending</h1>
            
            <div id="loading" class="hidden">
                <div class="flex justify-center items-center py-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Memproses pembayaran...</span>
                </div>
            </div>

            <div id="error-message" class="hidden mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            </div>
            
            @if(isset($pembayaranPending) && $pembayaranPending->count() > 0)
                <div class="space-y-4">
                    <p class="text-gray-600 mb-4">Berikut adalah daftar pesanan yang menunggu pembayaran:</p>
                    
                    @foreach($pembayaranPending as $pembayaran)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-semibold">ID Pesanan: #{{ $pembayaran->id_pemesanan }}</p>
                                    <p class="text-gray-600">Total: Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</p>
                                </div>
                                <button
                                    id="payButton"
                                    onclick="handlePayment('{{ $pembayaran->id_pemesanan }}')"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >
                                    Bayar Pesanan
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(isset($pembayaran))
                <div class="text-center">
                    <p class="text-gray-600 mb-4">Pembayaran untuk pesanan #{{ $pembayaran->id_pemesanan }} sedang diproses.</p>
                    <button
                        id="payButton"
                        onclick="handlePayment('{{ $pembayaran->id_pemesanan }}')"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Lanjutkan Pembayaran
                    </button>
                </div>
            @else
                <p class="text-center text-gray-600">Tidak ada pembayaran yang pending saat ini.</p>
            @endif
        </div>
    </div>

    <script>
function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function getJwtToken() {
    // Coba ambil token dari berbagai sumber
    let token = 
        sessionStorage.getItem('auth_token') || 
        sessionStorage.getItem('jwt_token') || 
        localStorage.getItem('auth_token');

    // Log untuk debugging
    console.log('Token sources:', {
        sessionStorage_auth_token: sessionStorage.getItem('auth_token'),
        sessionStorage_jwt_token: sessionStorage.getItem('jwt_token'),
        localStorage_auth_token: localStorage.getItem('auth_token')
    });
    console.log('Retrieved JWT Token:', token);

    // Tambahkan validasi token sederhana
    if (!token || token === 'undefined') {
        // Redirect ke halaman login jika token tidak valid
        window.location.href = '/login';
        return null;
    }

    return token;
}
        async function handlePayment(orderId) {
            const payButton = document.getElementById('payButton');
            payButton.disabled = true;
            // showLoading(true);

            try {
                const jwtToken = getJwtToken();
        
                // Tambahkan pengecekan token di awal
                if (!jwtToken) {
                    throw new Error('Token JWT tidak valid. Silakan login kembali.');
                }

                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Authorization': `Bearer ${jwtToken}`
                };

                // Debug: Log headers
                console.log('Request Headers:', headers);

                const response = await fetch(`/api/payments/get-token/${orderId}`, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'include'
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Gagal mendapatkan snap-token pembayaran');
                }

                const data = await response.json();
                console.log('snap', data)
                if (!data.data || !data.data.snap_token) {
                    throw new Error('Snap Token pembayaran tidak valid');
                }

                // Initialize Snap payment
                window.snap.pay(data.data.snap_token, {
                    onSuccess: function(result) {
                        // showLoading(false);
                        window.location.href = `/payment/success/${orderId}`;
                    },
                    onPending: function(result) {
                        // showLoading(false);
                        window.location.href = `/payment/waiting`;
                    },
                    onError: function(result) {
                        // showLoading(false);
                        window.location.href = `/payment/failed/${orderId}`;
                    },
                    onClose: function() {
                        // showLoading(false);
                        payButton.disabled = false;
                        console.log('Customer closed the popup without finishing the payment');
                    }
                });

            } catch (error) {
                // showLoading(false);
                payButton.disabled = false;
                console.error('Payment Error:', error);
                showError(error.message || 'Terjadi kesalahan saat memproses pembayaran');
            }
        }
    </script>
</body>
</html>