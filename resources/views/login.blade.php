<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <form id="loginForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
            
            <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    id="email" 
                    type="email" 
                    placeholder="Email"
                    required
                >
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" 
                    id="password" 
                    type="password" 
                    placeholder="Password"
                    required
                >
            </div>
            
            <div class="flex items-center justify-between">
                <button 
                    id="loginButton"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                    type="submit"
                >
                    Sign In
                </button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const loginButton = document.getElementById('loginButton');
        const errorMessage = document.getElementById('errorMessage');

        // Fungsi untuk menampilkan error
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('hidden');
        }

        // Fungsi untuk menyembunyikan error
        function hideError() {
            errorMessage.textContent = '';
            errorMessage.classList.add('hidden');
        }

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Reset error
            hideError();
            
            // Disable button saat proses login
            loginButton.disabled = true;
            loginButton.textContent = 'Logging in...';

            // Ambil nilai input
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            // Validasi input dasar
            if (!email || !password) {
                showError('Email dan password harus diisi');
                loginButton.disabled = false;
                loginButton.textContent = 'Sign In';
                return;
            }

            try {
                // Kirim request login
                const response = await fetch('/api/user/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });

                // Parse response
                const data = await response.json();

                // Cek response dari server
                if (data.status === 'success') {
                    // Simpan token dan informasi user di session storage
                    sessionStorage.setItem('auth_token', data.data.token);
                    sessionStorage.setItem('user_info', JSON.stringify(data.data.user));

                    // Log token dan user info (bisa dihapus di production)
                    console.log('Token berhasil disimpan:', data.data.token);
                    console.log('User Info:', data.data.user);

                    // Redirect ke dashboard
                    window.location.href = '/dashboard';
                } else {
                    // Tampilkan pesan error dari server
                    showError(data.message || 'Login gagal');
                }
            } catch (error) {
                // Tangani error jaringan atau parsing
                console.error('Login error:', error);
                showError('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                // Kembalikan tombol ke kondisi semula
                loginButton.disabled = false;
                loginButton.textContent = 'Sign In';
            }
        });

        // Fungsi logout (bisa dipanggil di halaman lain)
        window.logout = function() {
            // Hapus token dan user info dari session storage
            sessionStorage.removeItem('auth_token');
            sessionStorage.removeItem('user_info');
            // Redirect ke halaman login
            window.location.href = '/login';
        }

        // Fungsi untuk mendapatkan user info
        window.getUserInfo = function() {
            const userInfo = sessionStorage.getItem('user_info');
            return userInfo ? JSON.parse(userInfo) : null;
        }
    });
    </script>
</body>
</html>