<header class="header shop">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="api-base-url" content="{{ config('services.api_base_url') }}">  
<meta name="api-token" content="{{ session('auth.token') }}">
<!-- CSS SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<!-- JS SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Left -->
                    <div class="top-left">
                        <ul class="list-main">
                            <li><a href="https://wa.me/+6281238465833"><i class="ti-headphone-alt"></i> +62 81238465833</a></li>
                            <li><a href="mailto:radityana64@gmail.com"><i class="ti-email"></i> radityana64@gmail.com</a></li>
                        </ul>
                    </div>
                    <!--/ End Top Left -->
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Right -->
                    <div class="right-content">
                        <ul class="list-main" id="authSection">
                            <!-- This section will be dynamically populated by JavaScript -->
                        </ul>
                    </div>
                    <!-- End Top Right -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Topbar -->
    <div class="middle-inner">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-12">
                    <!-- Logo -->
                    <div class="logo">
                        <a href="/"><img src="/frontend/img/jaya%20logo.png" alt="logo"></a>
                    </div>
                    <!--/ End Logo -->
                    <div class="search-top">
                        <div class="top-search"><a href="#"><i class="ti-search"></i></a></div>
                        <!-- Search Form -->
                        <div class="search-top">
                            <form class="search-form">
                                <input type="text" placeholder="Cari Produk..." name="search">
                                <button value="search" type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                        <!--/ End Search Form -->
                    </div>
                    <div class="mobile-nav"></div>
                </div>
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="search-bar-top">
                        <div class="search-bar">
                        <form id="searchForm">
                            <input name="search" id="searchInput" placeholder="Cari Produk....." type="search">
                            <button class="btnn" type="submit"><i class="ti-search"></i></button>
                        </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12">
                    <div class="right-bar">
                        <!-- Cart -->
                        <div class="sinlge-bar shopping">
                            <a href="/keranjang" class="single-icon"><i class="ti-bag"></i> <span class="total-count" id="cartCount">0</span></a>
                            <!-- Shopping Item -->
                            <div class="shopping-item">
                                <div class="dropdown-cart-header">
                                    <span id="cartItemCount">0 Item</span>
                                    <span>di keranjang</span>
                                </div>
                                <ul class="shopping-list" id="cartList">
                                    <li>Loading cart items...</li>
                                </ul>
                                <div class="bottom">
                                    <a href="{{route('keranjang')}}" class="btn animate">Lihat Keranjang</a>
                                </div>
                            </div>
                            <!--/ End Shopping Item -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Header Inner -->
    <div class="header-inner">
        <div class="container">
            <div class="cat-nav-head">
                <div class="row">
                    <div class="col-lg-12 col-12">
                        <div class="menu-area">
                            <!-- Main Menu -->
                            <nav class="navbar navbar-expand-lg">
                                <div class="navbar-collapse">	
                                    <div class="nav-inner">	
                                        <ul class="nav main-menu menu navbar-nav">
                                            <li class="{{Request::path()=='/' ? 'active' : ''}}"><a href="/">Beranda</a></li>
                                            <li class="{{Request::path()=='about-us' ? 'active' : ''}}"><a href="/about-us">Tentang Jaya</a></li>
                                            <li class="{{Request::path()=='etalase/produk' ? 'active' : ''}}"><a href="/etalase/produk">Etalase Produk</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </nav>
                            <!--/ End Main Menu -->	
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ End Header Inner -->
</header>

<script>
    let cartData = null;
    let userProfile = null;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }

    function getApiBaseUrl() {
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }

    function handleSearch(event) {
        event.preventDefault(); // Mencegah pengiriman form default
        const searchInput = event.target.querySelector('input[name="search"]'); // Ambil input
        const searchTerm = searchInput ? searchInput.value.trim() : ''; // Ambil nilai input
        if (searchTerm) {
            window.location.href = `/etalase/produk/?search=${encodeURIComponent(searchTerm)}`;
        }
    }
    document.querySelector('.search-form').addEventListener('submit', handleSearch);
    document.getElementById('searchForm').addEventListener('submit', handleSearch);

    async function checkAuthStatus() {
        const jwtToken = getJwtToken();
        const metaToken = document.querySelector('meta[name="api-token"]');
        const authSection = document.getElementById('authSection');
        const apiBaseUrl = getApiBaseUrl();

        if (!jwtToken) {
            // User is not logged in
            authSection.innerHTML = `
                <li><a href="/login"><i class="ti-user"></i> Login</a> /<a href="/register">Register</a></li>
            `;
            return;
        }

        try {
            const response = await fetch(`${apiBaseUrl}/api/user/profil`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Authorization': `Bearer ${jwtToken}`
                }
            });

            if (response.status === 403) {
                metaToken.setAttribute('content', '');
                Swal.fire({
                    title: "Akses Ditolak!",
                    text: "Akun Anda telah dinonaktifkan oleh admin.",
                    icon: "warning",
                    timer: 3000, // Redirect setelah 3 detik
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "/login";
                });

                return;
            }

            // Simpan data profil ke variabel global
            const responseData = await response.json();
            userProfile = responseData.data; // Mengambil data dari respons

            // Emit event untuk menandakan data profil berhasil diambil
            document.dispatchEvent(new Event('profileFetched'));

            // Update tampilan UI berdasarkan profil pengguna
            authSection.innerHTML = `
                <li>
                    <a href="${userProfile.role === 'admin' || userProfile.role === 'pemilik_toko' ? '/admin' : '/data-pelanggan'}" id="userDashboard">
                        <i class="ti-user"></i> 
                        <span id="dashboardText">${userProfile.role === 'admin' || userProfile.role === 'pemilik_toko' ? 'Dashboard' : userProfile.pelanggan.username}</span>
                    </a>
                </li>
                <li><a href="/login" onclick="handleLogout(event)"><i class="ti-power-off"></i> Logout</a></li>
            `;
        } catch (error) {
            console.error('Error checking auth status:', error);

            // Jika terjadi error, tampilkan login/register
            authSection.innerHTML = `
                <li><a href="/login"><i class="ti-user"></i> Login</a> /<a href="/register">Register</a></li>
            `;
        }
    }

    async function handleLogout(event) {
        event.preventDefault();
        const jwtToken = getJwtToken();
        // console.log('jwt', jwtToken);
        const apiBaseUrl = getApiBaseUrl();

        try {
        // Logout API
            const apiResponse = await fetch(`${apiBaseUrl}/api/logout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Authorization': `Bearer ${jwtToken}`
                }
            });
            const apiData = await apiResponse.json();
            if (!apiResponse.ok) {
                throw new Error(apiData.message || 'API logout failed');
            }

            // Logout Web
            const webResponse = await fetch('http://127.0.0.1:8001/logout/session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const webData = await webResponse.json();
            if (!webResponse.ok) {
                throw new Error(webData.message || 'Web logout failed');
            }

            // Reset meta tag jika ada
            const metaApiToken = document.querySelector('meta[name="api-token"]');
            if (metaApiToken) {
                metaApiToken.setAttribute('content', '');
            }

            // Redirect ke login
            window.location.href = '/login';
        } catch (error) {
            console.error('Error during logout:', error);
            Swal.fire({
                title: "Logout Gagal!",
                text: `Terjadi kesalahan: ${error.message || 'Silakan coba lagi.'}`,
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    }

    function fetchCart() {
        const jwtToken = getJwtToken();
        const apiBaseUrl = getApiBaseUrl();
        
        if (!jwtToken) {
            console.error('Token JWT tidak valid. Silakan login kembali.');
            return;
        }

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Authorization': `Bearer ${jwtToken}`
        };

        fetch(`${apiBaseUrl}/api/keranjang`, {
            method: 'GET',
            headers: headers
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch cart');
            }
            return response.json();
        })
        .then(data => {
            cartData = data.cart; // Simpan data keranjang ke variabel global
            updateCartDisplay(cartData); // Tampilkan data keranjang
            document.dispatchEvent(new Event('cartFetched')); // Emit event
        })
        .catch(error => {
            console.error('Error fetching cart:', error);
            updateCartDisplay(null);
        });
    }

    function updateDashboardText(userData) {
        const dashboardText = document.getElementById('dashboardText');
        const dashboardLink = document.getElementById('userDashboard');
        
        if (userData) {
            if (userData.role === 'admin') {
                dashboardText.textContent = 'Dashboard';
                dashboardLink.href = '/welcome';
            } else {
                dashboardText.textContent = userData.username;
                dashboardLink.href = '/profil';
            }
        }
    }

    function updateCartDisplay(cartData) {
        const cartCount = document.getElementById('cartCount');
        const cartItemCount = document.getElementById('cartItemCount');
        const cartList = document.getElementById('cartList');
        const cartTotal = document.getElementById('cartTotal');

        if (!cartData || !cartData.detail_pemesanan || cartData.detail_pemesanan.length === 0) {
            cartCount.textContent = '0';
            cartItemCount.textContent = '0 Items';
            cartList.innerHTML = '<li>No items in cart</li>';
            cartTotal.textContent = 'Rp 0';
            return;
        }

        const totalItems = cartData.detail_pemesanan.reduce((total, item) => total + item.jumlah, 0);
        cartCount.textContent = totalItems;
        cartItemCount.textContent = `${totalItems} Items`;

        cartList.innerHTML = cartData.detail_pemesanan.map(item => `
            <li>
                <div class="cart-item" style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="flex: 1; text-align: left;">${item.produk_variasi.nama_produk}</span>
                    <span style="text-align: right; color: black; font-weight: bold;">
                        <span>${item.jumlah}</span> x 
                        <span>Rp ${item.produk_variasi.harga.toLocaleString('id-ID')}</span>
                    </span>
                </div>
            </li>
        `).join('');
    }

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        checkAuthStatus();
        fetchCart();
    });
</script>