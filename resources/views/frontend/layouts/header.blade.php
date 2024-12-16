<header class="header shop">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <img src="/frontend/img/jaya%20logo.jpg" alt="logo">
                    </div>
                    <!--/ End Logo -->
                    <div class="mobile-nav"></div>
                </div>
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="search-bar-top">
                        <div class="search-bar">
                        <form id="searchForm">
                            <input name="search" id="searchInput" placeholder="Search Products Here....." type="search">
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
                                    <span id="cartItemCount">0 Items</span>
                                    <a href="{{route('keranjang')}}">View Cart</a>
                                </div>
                                <ul class="shopping-list" id="cartList">
                                    <li>Loading cart items...</li>
                                </ul>
                                <div class="bottom">
                                    <div class="total">
                                        <span>Total</span>
                                        <span class="total-amount" id="cartTotal">$0.00</span>
                                    </div>
                                    <a href="/checkout" class="btn animate">Checkout</a>
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
                                            <li class=""><a href="/">Home</a></li>
                                            <li class=""><a href="#">About Us</a></li>
                                            <li class="active"><a href="/etalase/produk">Products</a><span class="new">New</span></li>												
                                            <li class=""><a href="#">Blog</a></li>	
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
<meta name="api-token" content="{{ session('auth.token') }}">
<script>
    let cartData = null;
    let userProfile = null;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }
    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Mencegah pengiriman form default
        const searchTerm = document.getElementById('searchInput').value.trim();
        if (searchTerm) {
            window.location.href = `/etalase/produk/?search=${encodeURIComponent(searchTerm)}`;
        }
    });

    async function checkAuthStatus() {
        const jwtToken = getJwtToken();
        const authSection = document.getElementById('authSection');

        if (!jwtToken) {
            // User is not logged in
            authSection.innerHTML = `
                <li><a href="/login"><i class="ti-user"></i> Login</a> /<a href="/register">Register</a></li>
            `;
            return;
        }

        try {
            const response = await fetch('http://127.0.0.1:8000/api/user/profil', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Authorization': `Bearer ${jwtToken}`
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch user profile');
            }

            // Simpan data profil ke variabel global
            const responseData = await response.json();
            userProfile = responseData.data; // Mengambil data dari respons

            // Emit event untuk menandakan data profil berhasil diambil
            document.dispatchEvent(new Event('profileFetched'));

            // Update tampilan UI berdasarkan profil pengguna
            authSection.innerHTML = `
                <li>
                    <a href="${userProfile.role === 'admin' ? '/admin' : '/data-pelanggan'}" id="userDashboard">
                        <i class="ti-user"></i> 
                        <span id="dashboardText">${userProfile.role === 'admin' ? 'Dashboard' : userProfile.pelanggan.username}</span>
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

        try {
            const response = await fetch('http://127.0.0.1:8000/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Authorization': `Bearer ${jwtToken}`
                }
            });

            const responseData = await response.json(); // Ambil data dari respons

            if (!response.ok) {
                throw new Error(responseData.message || 'Logout failed');
            }

            const metaApiToken = document.querySelector('meta[name="api-token"]');
            if (metaApiToken) {
                metaApiToken.setAttribute('content', ''); // Reset meta tag
            }

            // Redirect atau update UI setelah logout
            window.location.href = '/login'; // Redirect ke halaman login setelah logout

        } catch (error) {
            console.error('Error during logout:', error);
            alert('Logout failed. Please try again.');
        }
    }


    function fetchCart() {
        const jwtToken = getJwtToken();
        
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

        fetch('http://127.0.0.1:8000/api/keranjang', {
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
                <div class="cart-item">
                    <span>${item.produk_variasi.variasi}</span>
                    <span>${item.jumlah} x Rp ${item.produk_variasi.harga}</span>
                    <span>Rp ${item.sub_total_produk}</span>
                </div>
            </li>
        `).join('');

        cartTotal.textContent = `Rp ${cartData.total_harga}`;
    }

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        checkAuthStatus();
        fetchCart();
    });
</script>