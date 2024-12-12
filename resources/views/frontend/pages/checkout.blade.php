@extends('frontend.layouts.master')
@section('title', 'Checkout')
@section('main-content')
<div class="checkout-container py-4">
    <!-- Customer Information Section -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <div class="d-flex align-items-center">
                <h5 class="mb-0">Informasi Pelanggan</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="customer-info">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Username:</strong>
                        <span id="customerUsername"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Email:</strong>
                        <span id="customerEmail"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Telepon:</strong>
                        <span id="customerPhone"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Address Section -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 d-inline">Alamat Pengiriman</h5>
                </div>
                <button class="btn btn-outline-primary btn-sm" onclick="showAddressModal()">
                    Pilih Alamat
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="selectedAddress">
                <p class="text-muted">Silakan pilih alamat pengiriman</p>
            </div>
        </div>
    </div>

    <!-- Order Summary Section -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5>Produk Dipesan</h5>
        </div>
        <div class="card-body">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal Produk</th>
                    </tr>
                </thead>
                <tbody id="orderSummaryBody">
                    <!-- Data produk akan diisi di sini oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Shipping Options Section -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 d-inline">Opsi Pengiriman</h5>
                </div>
                <button class="btn btn-outline-primary btn-sm" onclick="showShippingOptionsModal()" id="chooseShippingBtn" disabled>
                    Pilih Opsi Pengiriman
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="selectedShippingOption">
                <p class="text-muted">Silakan pilih opsi pengiriman</p>
            </div>
        </div>
    </div>

    <!-- Address Selection Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Pilih Alamat Pengiriman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="addressList">
                    <!-- Address list will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Options Modal -->
    <div class="modal fade" id="shippingOptionsModal" tabindex="-1" aria-labelledby="shippingOptionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shippingOptionsModalLabel">Pilih Opsi Pengiriman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="shippingOptionsList">
                    <!-- Shipping options will be populated here -->
                </div>
            </div>
        </div>
    </div>

   <!-- Voucher Section -->
    <div class ="card mb-4">
        <div class="card-header bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 d-inline">Voucher</h5>
                </div>
                <button class="btn btn-outline-primary btn-sm" onclick="showvoucherModal()" id="chooseVoucherBtn">
                    Pilih Voucher
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="selectedVoucher">
                <p class="text-muted">Silakan pilih voucher</p>
            </div>
        </div>
    </div>

    <!-- Voucher Selection Modal -->
    <div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="voucherModalLabel">Pilih Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="voucherList">
                    <!-- Voucher list will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Order Total -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal Produk:</span>
                <span id="subtotalProduk">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Biaya Pengiriman:</span>
                <span id="biayaPengiriman">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2" id="voucherSection" style="display: none;">
                <span>Voucher:</span>
                <span id="voucherDiscount">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between border-top pt-2">
                <h6 class="mb-0">Total Pembayaran:</h6>
                <h6 class="text-danger mb-0" id="totalPembayaran">Rp 0</h6>
            </div>
        </div>
    </div>

    <!-- Checkout Button -->
    <div class="checkout-button-container mt-4">
        <button onclick="createPayment()" class="btn btn-danger w-100" id="buttonBayar">Buat Pesanan</button>
    </div>
</div>
@endsection

@push('styles')
<style>
    .transition-all {
    transition: all 0.3s ease;
}

.hover\:bg-gray-100:hover {
    background-color: #f8f9fa;
}

.cursor-pointer {
    cursor: pointer;
}
.checkout-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 15px;
}

.card {
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.address-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.address-card:hover {
    background-color: #f8f9fa;
    border-color: #primary;
}

.selected-address {
    border: 2px solid #007bff;
    background-color: #f8f9fa;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    padding: 15px;
}

.shop-icon {
    width: 20px;
    height: 20px;
    object-fit: cover;
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.product-title {
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.product-variant {
    font-size: 0.8rem;
}

.shipping-options {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.btn-link {
    color: #1a9cb7;
    text-decoration: none;
    padding: 0;
}

.btn-link:hover {
    color: #147885;
    text-decoration: underline;
}

.voucher-section {
    background-color: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.checkout-button-container {
    position: sticky;
    bottom: 0;
    background-color: white;
    padding: 15px 0;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .checkout-container {
        padding: 0 10px;
    }

    .product-title {
        font-size: 0.85rem;
    }

    .product-image {
        width: 50px;
        height: 50px;
    }
}
</style>
@endpush

@push('scripts')
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-1JTCrR9hP3kq-wie"></script>
<!-- <script src="https://app.midtrans.com/snap/snap.js"></script> -->
<script>
    
    let selectedAddressId = null;
    let selectedShippingOption = null;
    let selectedVoucherId = null;
    let subtotal = 0;
    let shippingCost = 0;
    let voucherDiscount = 0;
    let calculatedVoucherDiscount = 0;
    let dataPelanggan = {};

    document.addEventListener('cartFetched', function() {
        populateCheckoutWithCart(); // Panggil populate setelah cart di-fetch
        loadCustomerInfo();
        loadAddresses();
        // updateTotalPayment()
    });

    async function loadCustomerInfo() {
        const jwtToken = getJwtToken();
        try {
            const response = await fetch('http://127.0.0.1:8000/api/pelanggan/profil', {
                headers: {
                    'Authorization': `Bearer ${jwtToken}`,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch profile');
            
            dataPelanggan = await response.json(); // Simpan data pelanggan ke variabel global
            
            // Populate customer info
            document.getElementById('customerUsername').textContent = dataPelanggan.data.pelanggan.username || '-';
            document.getElementById('customerEmail').textContent = dataPelanggan.data.email || '-';
            document.getElementById('customerPhone').textContent = dataPelanggan.data.pelanggan.telepon || '-';
        } catch (error) {
            console.error('Error loading customer info:', error);
        }
    }

    async function loadAddresses() {
    const jwtToken = getJwtToken();
    const addressList = document.getElementById('addressList');
    
    // Add loading state
    addressList.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat alamat...</p>
        </div>
    `;

    try {
        const response = await fetch('http://127.0.0.1:8000/api/alamat', {
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to fetch addresses');
        
        const data = await response.json();
        
        if (data.status !== 'success' || data.data.length === 0) {
            addressList.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-muted">Tidak ada alamat tersimpan</p>
                    <button class="btn btn-primary mt-2" href="/data-pengguna/alamat">alamat</button>
                </div>
            `;
            return;
        }

        addressList.innerHTML = data.data.map(address => `
            <div class="address-card transition-all hover:bg-gray-100 cursor-pointer p-3 rounded-lg mb-2" 
                 onclick="selectAddress(${JSON.stringify(address).replace(/"/g, '&quot;')})">
                <h6 class="mb-2">${address.nama_jalan}</h6>
                <p class="mb-1">${address.detail_lokasi}</p>
                <p class="mb-1">${address.kode_pos.nama_kota}, ${address.kode_pos.nama_provinsi}</p>
                <p class="mb-0">${address.kode_pos.kode_pos}</p>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading addresses:', error);
        addressList.innerHTML = `
            <div class="text-center py-4">
                <p class="text-danger">Gagal memuat alamat. Silakan coba lagi.</p>
                <button class="btn btn-primary mt-2" onclick="loadAddresses()">Coba Lagi</button>
            </div>
        `;
    }
}


    function showAddressModal() {
        const modal = new bootstrap.Modal(document.getElementById('addressModal'));
        modal.show();
    }

    function selectAddress(address) {
        const selectedAddressDiv = document.getElementById('selectedAddress');
        selectedAddressDiv.innerHTML = `
            <div class="selected-address-details">
                <h6 class="mb-2">${address.nama_jalan}</h6>
                <p class="mb-1">${address.detail_lokasi}</p>
                <p class="mb-1">${address.kode_pos.nama_kota}, ${address.kode_pos.nama_provinsi}</p>
                <p class="mb-0">${address.kode_pos.kode_pos}</p>
            </div>
        `;
        
        // Set selected address ID and enable shipping options button
        selectedAddressId = address.id_alamat;
        document.getElementById('chooseShippingBtn').disabled = false;

        // Post selected address
        postSelectedAddress(selectedAddressId);
    }

    async function postSelectedAddress(addressId) {
        const jwtToken = getJwtToken();
        try {
            const response = await fetch('http://127.0.0.1:8000/api/pilih-alamat-pengiriman', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${jwtToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id_alamat: addressId })
            });
            
            if (!response.ok) throw new Error('Failed to select address');
        } catch (error) {
            console.error('Error selecting address:', error);
        }
    }

    function showShippingOptionsModal() {
        loadShippingOptions();
        const modal = new bootstrap.Modal(document.getElementById('shippingOptionsModal'));
        modal.show();
    }

    async function loadShippingOptions() {
        const jwtToken = getJwtToken();
        const shippingOptionsList = document.getElementById('shippingOptionsList');
        
        // Add loading state
        shippingOptionsList.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat opsi pengiriman...</p>
            </div>
        `;

        try {
            const response = await fetch('http://127.0.0.1:8000/api/opsi-pengiriman', {
                headers: {
                    'Authorization': `Bearer ${jwtToken}`,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch shipping options');
            
            const data = await response.json();
            
            if (data.shipping_options.length === 0) {
                shippingOptionsList.innerHTML = `
                    <div class="text-center py-4">
                        <p class="text-muted">Tidak ada opsi pengiriman tersedia</p>
                    </div>
                `;
                return;
            }

            shippingOptionsList.innerHTML = data.shipping_options.map(carrier => `
                <div class="carrier-section mb-3">
                    <h6>${carrier.name}</h6>
                    ${carrier.costs.length > 0 ? carrier.costs.map(service => `
                        <div class="shipping-option transition-all hover:bg-gray-100 cursor-pointer p-3 rounded-lg mb-2" 
                            onclick="selectShippingOption('${carrier.code}', '${service.service}', ${service.cost[0].value}, '${service.description}', '${service.cost[0].etd}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${service.service} - ${service.description}</strong>
                                    <p class="text-muted mb-0">Estimasi: ${service.cost[0].etd} hari</p>
                                </div>
                                <div class="text-end">
                                    <strong>Rp ${formatNumber(service.cost[0].value)}</strong>
                                </div>
                            </div>
                        </div>
                    `).join('') : `
                        <p class="text-muted text-center py-3">Tidak ada layanan pengiriman tersedia untuk kurir ini</p>
                    `}
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading shipping options:', error);
            shippingOptionsList.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-danger">Gagal memuat opsi pengiriman. Silakan coba lagi.</p>
                </div>
            `;
        }
    }

    function selectShippingOption(carrierCode, serviceCode, cost, description, etd) {
        const selectedShippingDiv = document.getElementById('selectedShippingOption');
        selectedShippingDiv.innerHTML = `
            <div class="selected-shipping-details">
                <h6>${carrierCode.toUpperCase()} - ${serviceCode}</h6>
                <p class="mb-1">${description}</p>
                <p class="mb-1">Estimasi: ${etd} hari</p>
                <p class="mb-0">Biaya: Rp ${formatNumber(cost)}</p>
            </div>
        `;
        
        selectedShippingOption = {
            carrier_code: carrierCode,
            service_code: serviceCode,
            cost: cost,
            description: description,
            etd: etd
        };
        shippingCost = cost;
        
        updateTotalPayment(); // Update total payment after selecting shipping
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    async function loadVouchers() {
        const jwtToken = getJwtToken();
        const voucherList = document.getElementById('voucherList');
        
        // Add loading state
        voucherList.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat voucher...</p>
            </div>
        `;

        try {
            const response = await fetch('http://127.0.0.1:8000/api/vouchers/active', {
                headers: {
                    'Authorization': `Bearer ${jwtToken}`,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch vouchers');
            
            const data = await response.json();
            
            if (!data.success || data.data.length === 0) {
                voucherList.innerHTML = `
                    <div class="text-center py-4">
                        <p class="text-muted">Tidak ada voucher aktif</p>
                    </div>
                `;
                return;
            }

            voucherList.innerHTML = data.data.map(voucherData => {
                const voucher = voucherData.voucher;
                return `
                    <div class="voucher-card transition-all hover:bg-gray-100 cursor-pointer p-3 rounded-lg mb-2" 
                        onclick="selectVoucher(${JSON.stringify(voucher).replace(/"/g, '&quot;')})">
                        <h6 class="mb-2">${voucher.nama_voucher}</h6>
                        <p class="mb-1">Diskon: ${voucher.diskon} %</p>
                        <p class="mb-1">Min Pembelian: Rp ${formatNumber(voucher.min_pembelian)}</p>
                        <p class="mb-0">Tanggal Akhir: ${new Date(voucher.tanggal_akhir).toLocaleDateString()}</p>
                    </div>
                `;
            }).join('');
        } catch (error) {
            console.error('Error loading vouchers:', error);
            voucherList.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-danger">Gagal memuat voucher. Silakan coba lagi.</p>
                </div>
            `;
        }
    }

    function selectVoucher(voucher) {
        const selectedVoucherDiv = document.getElementById('selectedVoucher');
        selectedVoucherDiv.innerHTML = `
            <div class="selected-voucher-details">
                <h6 class="mb-2">${voucher.nama_voucher}</h6>
                <p class="mb-1">Diskon: ${voucher.diskon} %</p>
                <p class="mb-1">Min Pembelian: Rp ${formatNumber(voucher.min_pembelian)}</p>
                <p class="mb-0">Tanggal Akhir: ${new Date(voucher.tanggal_akhir).toLocaleDateString()}</p>
            </div>
        `;
        
        selectedVoucherId = voucher.id_voucher;
        voucherDiscount = voucher.diskon;
        
        updateTotalPayment(); // Update total payment after selecting voucher
    }

    function showvoucherModal() {
        loadVouchers(); // Pastikan untuk memanggil loadVouchers
        const modal = new bootstrap.Modal(document.getElementById('voucherModal'));
        modal.show();
    }

    function populateCheckoutWithCart() {
        const orderSummaryBody = document.getElementById('orderSummaryBody');
        subtotal = 0;
        // Cek apakah cartData ada
        if (cartData && cartData.detail_pemesanan.length > 0) {
            cartData.detail_pemesanan.forEach(item => {
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td>
                        <div class="product-info">
                            <div class="product-details">
                                <p>${item.produk_variasi.nama_produk}</p>
                                <p>Variasi: ${item.produk_variasi.variasi || '-'}</p>
                            </div>
                        </div>  
                    </td>
                    <td>Rp ${item.produk_variasi.harga.toLocaleString()}</td>
                    <td>${item.jumlah}</td>
                    <td>Rp ${item.sub_total_produk.toLocaleString()}</td>
                `;

                orderSummaryBody.appendChild(row);
                subtotal += item.sub_total_produk;
            });
            // Update total pembayaran setelah menghitung subtotal
            console.log("Subtotal calculated:", subtotal);
            updateTotalPayment();
        } else {
            console.log('Cart data is not available.');
        }
    }
    // <img src="${item.produk_variasi.gambar}" alt="${item.produk_variasi.nama_produk}" class="product-image">
    
    function updateTotalPayment() {
        const subtotalElement = document.getElementById('subtotalProduk');
        const shippingElement = document.getElementById('biayaPengiriman');
        const voucherElement = document.getElementById('voucherDiscount');
        const totalElement = document.getElementById('totalPembayaran');
        const voucherSection = document.getElementById('voucherSection');

        // Format subtotal
        subtotalElement.textContent = `Rp ${formatNumber(subtotal)}`;
        
        // Format shipping cost
        shippingElement.textContent = `Rp ${formatNumber(shippingCost)}`;
        
        // Calculate and format voucher discount
        
        if (voucherDiscount > 0) {
            calculatedVoucherDiscount = (voucherDiscount / 100) * subtotal;
            voucherElement.textContent = `Rp ${formatNumber(calculatedVoucherDiscount)}`;
            voucherSection.style.display = 'flex';
        } else {
            voucherSection.style.display = 'none';
        }
        
        // Calculate and format total
        const total = subtotal + shippingCost - calculatedVoucherDiscount;
        totalElement.textContent = `Rp ${formatNumber(total)}`;
    }

    async function createPayment() {
    const jwtToken = getJwtToken();
    const selectedAddress = document.getElementById('selectedAddress');

    if (!selectedAddress || !selectedAddress.innerText.trim()) {
        alert('Silakan pilih alamat pengiriman');
        return;
    }

    if (!selectedShippingOption) {
        alert('Silakan pilih opsi pengiriman');
        return;
    }

    // Memastikan semua item memiliki data yang diperlukan
    const validItems = cartData.detail_pemesanan
        .filter(item => item?.produk_variasi?.id_produk_variasi && item?.produk_variasi?.harga && item.jumlah)
        .map(item => ({
            id: item.produk_variasi.id_produk_variasi,
            price: Math.round(item.produk_variasi.harga),
            quantity: item.jumlah,
            name: item.produk_variasi.nama_produk || `Product ${item.produk_variasi.id_produk_variasi}`
        }));

    if (validItems.length === 0) {
        alert('Data produk tidak valid');
        return;
    }

    const orderData = {
        order_id: String(cartData.id_pemesanan), // Ubah order_id menjadi string
        total_amount: Math.round(subtotal + shippingCost - calculatedVoucherDiscount), 
        items: validItems,
        address: selectedAddress.innerText.replace(/\n/g, ', ').replace(/,+/g, ', ').trim(), // Ganti \n dengan koma dan hapus koma berlebih
        shipping_cost: Math.round(shippingCost),
        voucher_discount: Math.round(calculatedVoucherDiscount),
        firstName: dataPelanggan.data.pelanggan.username || '',
        email: dataPelanggan.data.email || '',
        phone: dataPelanggan.data.pelanggan.telepon || '',
    };

    // Tambahkan console.log untuk melihat data yang dikirim
    console.log('Sending Order Data:', orderData);

    try {
        const response = await fetch('http://127.0.0.1:8000/api/payments/create-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${jwtToken}`,
            },
            body: JSON.stringify(orderData),
        });

        // Ambil response text untuk debugging
        const responseText = await response.text();
        console.log('Response Text:', responseText);

        let responseData;
        try {
            responseData = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse response:', parseError);
            throw new Error('Invalid response from server');
        }

        if (!response.ok) {
            throw new Error(responseData.message || 'Failed to create payment');
        }

        console.log('Snap Token:', responseData.snap_token);

        if (window.snap) {
            window.snap.pay(responseData.snap_token, {
                onSuccess: async function(result) {
                    console.log('Payment Success:', result);
                    await saveShippingAndVoucher();
                    window.location.href = '/data-pelanggan/pesanan';
                },
                onPending: async function(result) {
                    console.log('Payment Pending:', result);
                    try {
                        await sendSnapToken(responseData.snap_token, jwtToken);
                        await saveShippingAndVoucher();
                        window.location.href = '/data-pelanggan/pesanan';
                        
                    } catch (error) {
                        console.error('Error sending Snap token:', error);
                        alert(error.message);
                    }
                },
                onError: function(result) {
                    console.log('Payment Error:', result);
                },
                onClose: function() {
                    console.log('Payment Popup Closed');
                }
            });
        } else {
            console.error('Midtrans Snap.js not loaded');
        }
    } catch (error) {
        console.error('Error creating payment:', error);
        alert(error.message);
    }
}


async function saveShippingAndVoucher() {
    const jwtToken = getJwtToken();

    // Simpan voucher jika dipilih
    if (selectedVoucherId) {
        try {
            const voucherResponse = await fetch('http://127.0.0.1:8000/api/vouchers/gunakan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${jwtToken}`,
                },
                body: JSON.stringify({
                    id_pemesanan: cartData.id_pemesanan,
                    id_voucher: selectedVoucherId,
                    jumlah_diskon: calculatedVoucherDiscount
                })
            });

            if (!voucherResponse.ok) {
                const errorText = await voucherResponse.text();
                console.error('Voucher save error:', errorText);
                // Tidak perlu throw error agar proses tetap berlanjut
            }
        } catch (error) {
            console.error('Error saving voucher:', error);
        }
    }

    // Simpan jasa pengiriman
    if (selectedShippingOption) {
        try {
            const shippingResponse = await fetch('http://127.0.0.1:8000/api/pilih-jasa', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${jwtToken}`,
                },
                body: JSON.stringify({
                    kurir: selectedShippingOption.carrier_code,
                    layanan: selectedShippingOption.service_code,
                    estimasi_pengiriman: selectedShippingOption.etd,
                    biaya_pengiriman: selectedShippingOption.cost
                })
            });

            if (!shippingResponse.ok) {
                const errorText = await shippingResponse.text();
                console.error('Shipping save error:', errorText);
                // Tidak perlu throw error agar proses tetap berlanjut
            }
        } catch (error) {
            console.error('Error saving shipping service:', error);
        }
    }
}

async function sendSnapToken(snap_token) {
    const response = await fetch('http://127.0.0.1:8000/api/payments/snap', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            snap_token: snap_token, // Kirim Snap token
            // Anda dapat menambahkan order_id jika diperlukan
            order_id: cartData.id_pemesanan, // Misalnya jika Anda memiliki order_id
        }),
    });

    // Cek response
    if (!response.ok) {
        const responseText = await response.text();
        throw new Error(`Failed to send Snap token: ${responseText}`);
    }

    return await response.json(); // Kembalikan response jika perlu
}

</script>
@endpush