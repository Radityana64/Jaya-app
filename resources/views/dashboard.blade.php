<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-1JTCrR9hP3kq-wie"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <button
            onclick="initiatePayment()"
            class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 ease-in-out hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
            id="payButton"
        >
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Bayar Sekarang
            </span>
        </button>
        <button
            onclick="handlePaymentPending()"
            class="bg-red-600 text-white px-10 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 ease-in-out hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
            id="payButton"
        >
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Belum Bayar
            </span>
        </button>
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

    // Tambahkan pengecekan tambahan
    if (!token) {
        // Coba ambil dari data login yang disimpan
        const userInfoStr = sessionStorage.getItem('user_info');
        if (userInfoStr) {
            try {
                const userInfo = JSON.parse(userInfoStr);
                token = userInfo.token; // Sesuaikan dengan struktur respons login Anda
            } catch (error) {
                console.error('Error parsing user info:', error);
            }
        }
    }

    // Log token yang ditemukan
    console.log('Retrieved JWT Token:', token);

    // Tambahkan validasi token sederhana
    if (!token || token === 'undefined') {
        // Redirect ke halaman login jika token tidak valid
        window.location.href = '/login';
        return null;
    }

    return token;
}
        async function initiatePayment() {
            const payButton = document.getElementById('payButton');
    payButton.disabled = true;

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

        const cartResponse = await fetch('/api/keranjang', {
            method: 'GET',
            headers: headers
        });

        // Debug: Log response status
        console.log('Response Status:', cartResponse.status);

        // Tangani unauthorized error
        if (cartResponse.status === 401) {
            // Token mungkin expired atau tidak valid
            alert('Sesi Anda telah berakhir. Silakan login kembali.');
            window.location.href = '/login';
            return;
        }

        // Tangkap error response
        if (!cartResponse.ok) {
            const errorText = await cartResponse.text();
            console.error('Error Response:', errorText);
            
            throw new Error(`HTTP error! status: ${cartResponse.status}, message: ${errorText}`);
        }

        const responseData = await cartResponse.json();
        console.log('Keranjang Response:', responseData);

        // Pastikan struktur data sesuai
        const orderId = responseData.cart.id_pemesanan;

                const paymentResponse = await fetch(`/api/payments/create/${orderId}`, {
                    method: 'POST',
                    headers: headers,
                    credentials: 'include'
                });

                if (!paymentResponse.ok) {
                    const errorText = await paymentResponse.text();
                    console.error('Payment Response not OK:', paymentResponse.status, errorText);
                    throw new Error(`HTTP error! status: ${paymentResponse.status}`);
                }
                
                const paymentData = await paymentResponse.json();
                console.log('Payment response:', paymentData);

                // Check for snap token in the response
                if (!paymentData.snap_token) {
                    console.error('Invalid payment data:', paymentData);
                    throw new Error('Invalid payment data: missing snap token');
                }

                // Menampilkan popup pembayaran Midtrans
                window.snap.pay(paymentData.snap_token, {
                    // enabledPayments: paymentData.payment_method ? [paymentData.payment_method] : null,
                    onSuccess: function(result) {
                        console.log('Payment success:', result);
                        handlePaymentSuccess(orderId, result);
                    },
                    onPending: function(result) {
                        console.log('Payment pending:', result);
                        handlePaymentPending(orderId, result);
                    },
                    onError: function(result) {
                        console.error('Payment error:', result);
                        handlePaymentError(orderId, result);
                    },
                    onClose: function() {
                        console.log('Payment window closed');
                        payButton.disabled = false;
                        showAlert('Pembayaran dibatalkan', 'warning');
                    }
                });

            } catch (error) {
                console.error('Payment Error:', error);
                payButton.disabled = false;
                showAlert(error.message || 'Terjadi kesalahan saat memproses pembayaran', 'error');
            }
        }

        function handlePaymentPending() {
            showAlert('Pembayaran sedang diproses', 'info');
            setTimeout(() => {
                window.location.href = `/payment/waiting`;
            }, 2000);
        }

        function handlePaymentError(orderId) {
            showAlert('Pembayaran gagal', 'error');
            setTimeout(() => {
                window.location.href = `/payment/failed/${orderId}`;
            }, 2000);
        }

        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${getAlertClass(type)}`;
            alertDiv.textContent = message;
            document.body.appendChild(alertDiv);
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        function getAlertClass(type) {
            switch (type) {
                case 'success':
                    return 'bg-green-500 text-white';
                case 'error':
                    return 'bg-red-500 text-white';
                case 'warning':
                    return 'bg-yellow-500 text-white';
                default:
                    return 'bg-blue-500 text-white';
            }
        }
    </script>
</body>
</html>
