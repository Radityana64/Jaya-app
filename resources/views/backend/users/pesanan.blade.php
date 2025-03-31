@extends('backend.layouts.master')

@section('title', 'Admin Profile')

@section('main-content')

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="daftar-nama m-0 font-weight-bold text-primary">Daftar Pesanan</h6>
    </div>
    <div class="card-body data-pesanan">
        <form id="search-form" class="mb-3 d-flex">
            <input type="text" id="search-input" class="form-control" placeholder="Cari pesanan...">
            <button type="submit" id="search-button" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i> <!-- Ikon pencarian -->
            </button>
        </form>
        <div class="tabs">
            <div class="nav-tabs" id="order-tabs">
                <a class="nav-link" href="#" data-status="Semua">
                    <span>Semua</span>
                </a>
                <a class="nav-link" href="#" data-status="Belum Bayar">
                    <span>Belum Bayar</span>
                </a>
                <a class="nav-link" href="#" data-status="Dikemas">
                    <span>Dikemas</span>
                </a>
                <a class="nav-link" href="#" data-status="Dikirim">
                    <span>Dikirim</span>
                </a>
                <a class="nav-link" href="#" data-status="Selesai">
                    <span>Selesai</span>
                </a>
                <a class="nav-link" href="#" data-status="Dibatalkan">
                    <span>Dibatalkan</span>
                </a>
            </div>
        </div>

        <div id="order-list" class="order-list">
            <!-- Daftar pesanan -->
        </div>
    </div>
</div>
<div id="detail-pesanan-content" class="order-detail" style="display: none; padding: 30px">
    <div id="detail-pesanan"></div>
</div>
<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Penilaian Produk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <!-- Detail penilaian akan dimasukkan di sini -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Sticky Navbar */
    .tabs {
        position: sticky;
        top: 0px; /* Sesuaikan dengan tinggi navbar */
        background: white;
        z-index: 1000;
        border-bottom: 1px solid #ddd;
        display: flex;
        gap: 10px;
    }

    .nav-tabs {
        display: flex;
        justify-content: space-between; /* Membuat elemen tersebar merata */
        width: 100%;
        padding: 0;
        margin: 0;
    }

    .nav-tabs .nav-item {
        flex-grow: 1; /* Membuat setiap tab memiliki lebar yang sama */
        text-align: center;
    }

    .nav-tabs .nav-link {
        display: block;
        width: 100%;
        text-decoration: none;
        padding: 10px;
        border-bottom: 2px solid transparent;
        transition: color 0.3s ease-in-out, border-bottom 0.3s ease-in-out;
        color: #000000; /* Warna teks default */
        position: relative;
        text-align: center;
    }

    .nav-tabs .nav-link.active {
        font-weight: bold;
    }

    /* Underline untuk tab aktif */
    .nav-tabs .nav-link.active::after {
        content: '';
        display: block;
        width: 100%;
        height: 2px;
        position: absolute;
        bottom: -1px;
        left: 0;
    }

    .order-list {
        margin-top: 20px;
    }

    .order-item {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .order-item h4 {
        margin: 0 0 10px;
    }

    .order-item p {
        margin: 5px 0;
    }

    .border-atas-bawah {
        border-top: 1px solid #ccc; /* Warna dan ketebalan garis atas */
        border-bottom: 1px solid #ccc; /* Warna dan ketebalan garis bawah */
        padding-top: 10px; /* Tambahkan ruang di atas konten */
        padding-bottom: 10px; /* Tambahkan ruang di bawah konten */
    }

</style>
@endpush

@push('scripts')
<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
    
    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }
    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }
    function formatWhatsAppLink(phone) {
        const cleanPhone = phone.replace(/\D/g, '');
        return `https://wa.me/${cleanPhone}`;
    }
    const idPelanggan = window.location.pathname.split('/').pop();

    document.addEventListener("DOMContentLoaded", async function() {
        const tabs = document.querySelectorAll("#order-tabs .nav-link");
        const orderList = document.getElementById("order-list");

        // const modal = new bootstrap.Modal(document.getElementById("orderDetailModal"));
        const reviewModal = new bootstrap.Modal(document.getElementById("reviewModal"));
        
        let replyCache = {};
        let allOrders = [];

        async function fetchOrders() {
            const url = `${getApiBaseUrl()}/api/pemesanan/data/pelanggan/${idPelanggan}`;
            try {
                const response = await fetch(url, {
                    headers: {
                        "Authorization": `Bearer ${getJwtToken()}`,
                        "Content-Type": "application/json"
                    }
                });
                if (response.status === 404) {
                    return []; // Mengembalikan array kosong jika 404 (Not Found)
                }

                if (!response.ok) {
                    throw new Error(`Gagal mengambil data, status: ${response.status}`);
                }

                const result = await response.json();
                if (!result || !result.data || result.data.length === 0) {
                    return []; // Mengembalikan array kosong jika data tidak ada
                }
                return result.data;
            } catch (error) {
                console.error("Error fetching orders:", error);
                return [];
            }
        }

        
        function updateURL(status) {
            const url = new URL(window.location);
            url.searchParams.set("status", status);
            window.history.pushState({}, "", url);
        }

        function filterBanner(pesanan) {
            if (pesanan.pembayaran?.status_pembayaran === 'Pending') {
                return { text: 'Proses Pembayaran', color: 'text-warning' };
            }
            if (pesanan.pengiriman?.status_pengiriman === 'Dikemas' && pesanan.status_pemesanan !== 'Pesanan_Dibatalkan') {
                return { text: 'Dikemas', color: 'text-primary' };
            }
            if (pesanan.pengiriman?.status_pengiriman === 'Dikirim'&& pesanan.status_pemesanan !== 'Pesanan_Dibatalkan') {
                return { text: 'Dikirim', color: 'text-info' };
            }
            if (pesanan.pengiriman?.status_pengiriman === 'Diterima') {
                return { text: 'Pesanan Diterima', color: 'text-success' };
            }
            if (pesanan.pembayaran?.status_pembayaran === 'Gagal') {
                return { text: 'Gagal', color: 'text-danger' };
            }
            return { text: 'Unknown', color: 'text-secondary' };
        }

        async function renderPelanggan() {
            const url = `${getApiBaseUrl()}/api/pelanggan/data/${idPelanggan}`;
            try {
                const response = await fetch(url, {
                    headers: {
                        "Authorization": `Bearer ${getJwtToken()}`,
                        "Content-Type": "application/json"
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const Pelanggan = await response.json();
                console.log("Response Pelanggan:", Pelanggan); // Tambahkan log ini
                if ( Pelanggan.data.username) {
                    const namaPelanggan = Pelanggan.data.username;
                    const daftarPesananElement = document.querySelector(".daftar-nama");
                    daftarPesananElement.textContent = `Daftar Pesanan ${namaPelanggan}`;
                    return Pelanggan.data;
                }
            } catch (error) {
                console.error("Error fetching pelanggan:", error);
                return null;
            }
        }

        document.getElementById("search-form").addEventListener("submit", function (event) {
            event.preventDefault(); // Mencegah reload halaman saat submit form
            const searchQuery = document.getElementById("search-input").value.toLowerCase().trim();
            searchOrders(searchQuery);
        });

        function searchOrders(query) {
            if (!query) {
                renderOrders(); // Jika input kosong, kembalikan tampilan semua pesanan
                return;
            }
        
            // Pindahkan tab ke "Semua"
            setActiveTab("Semua");

            const filteredOrders = allOrders.daftar_pemesanan.filter(order => {
                const idMatch = order.id_pemesanan.toString().includes(query);
                const productMatch = order.detail_pemesanan.some(detail =>
                    detail.produk_variasi.nama_produk.toLowerCase().includes(query)
                );
                return idMatch || productMatch;
            });

            renderOrders("Semua", filteredOrders);
        }

        function renderOrders(status = "Semua", filteredOrders = null) {
            orderList.innerHTML = "";

            let ordersToRender = filteredOrders ? filteredOrders : allOrders.daftar_pemesanan.filter((order) => {
                if (status === "Semua") return true;
                if (status === "Belum Bayar") return order.pembayaran?.status_pembayaran === "Pending";
                if (status === "Dikemas") return order.pengiriman?.status_pengiriman === "Dikemas" && order.status_pemesanan !== "Pesanan_Dibatalkan";
                if (status === "Dikirim") return order.pengiriman?.status_pengiriman === "Dikirim" && order.status_pemesanan !== "Pesanan_Dibatalkan";
                if (status === "Selesai") return order.status_pemesanan === "Pesanan_Diterima";
                if (status === "Dibatalkan") return order.status_pemesanan === "Pesanan_Dibatalkan";
                return false;
            });

            if (ordersToRender.length === 0) {
                orderList.innerHTML = `<p class="text-center">Tidak ada pesanan</p>`;
                return;
            }

            ordersToRender.forEach(order => {
                const statusInfo = filterBanner(order);
                const orderItem = document.createElement("div");
                orderItem.className = "order-item card p-3 mb-3 shadow-sm";
                orderItem.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <p>Pesanan #${order.id_pemesanan}</p>
                        <div class="d-flex"> 
                            <p class="text-muted mr-2">${order.tanggal_pemesanan}</p>
                            <p><strong></strong> <span class="${statusInfo.color}">${statusInfo.text}</span></p>
                        </div>
                    </div>
                    <div class="order-products p-2 border-atas-bawah">
                        ${order.detail_pemesanan.map(d => `
                            <div class="d-flex align-items-center mb-2 p-2">
                                <img src="${d.produk_variasi.gambar}" alt="${d.produk_variasi.nama_produk}" class="rounded" width="50" height="50">
                                <div class="flex-grow-1 px-2">
                                    <p class="mb-0">${d.produk_variasi.nama_produk}</p>
                                    <p class="text-muted small mb-1">${d.produk_variasi.variasi || ""}</p>
                                </div>
                                <p class="mb-0"><strong>Rp ${d.produk_variasi.harga.toLocaleString()} x ${d.jumlah}</strong></p>
                            </div>
                        `).join("")}
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <p class="mb-0"><strong>Rp ${order.pembayaran.total_pembayaran.toLocaleString()}</strong></p>
                        <div class="d-flex"> 
                            <button class="btn btn-primary btn-sm show-details-btn" data-id="${order.id_pemesanan}">Tampilkan Detail</button>
                            ${
                                order.status_pemesanan !== "Pesanan_Dibatalkan" && order.pembayaran?.status_pembayaran === "Pending"
                                    ? `<button class="btn btn-danger btn-sm ml-2 cencel-btn" data-id="${order.pembayaran.id_transaksi_midtrans}">Batalkan Pesanan</button>`
                                    : ""
                            }
                            ${
                                order.status_pemesanan !== "Pesanan_Dibatalkan" && order.pengiriman?.status_pengiriman === "Dikemas"
                                    ? `<button class="btn btn-danger btn-sm ml-2 batalkan-btn" data-id="${order.id_pemesanan}">Batalkan</button>`
                                    : ""
                            }
                            ${
                                order.status_pemesanan !== "Pesanan_Dibatalkan" && order.pengiriman?.status_pengiriman === "Dikemas"
                                    ? `<button class="btn btn-success btn-sm ml-2 kirim-pesanan" data-id="${order.pengiriman.id_pengiriman}">Kirim</button>`
                                    : ""
                            }
                        </div>
                    </div>
                `;
                orderList.appendChild(orderItem);
            });

            document.querySelectorAll(".show-details-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const orderId = this.getAttribute("data-id");
                    const pelanggan = {
                        nama_pelanggan: allOrders.nama_pelanggan,
                        telepon: allOrders.telepon
                    };  
                    const order = allOrders.daftar_pemesanan.find((o) => o.id_pemesanan == orderId);
                    showOrderDetails(order, pelanggan);
                });
            });
        }

        $(document).on('click', '.review-button-variation', function() {
            const orderId = $(this).data('id'); // Gunakan jQuery untuk ambil data-id
            const order = allOrders?.daftar_pemesanan?.find((o) => o.id_pemesanan == orderId);
            const detail = order?.detail_pemesanan || [];

            if (order) {
                showReviewModal(orderId, detail);
            } else {
                console.error(`Detail pesanan dengan ID ${orderId} tidak ditemukan.`);
            }
        });

        function showOrderDetails(order, pelanggan) {
            const statusInfo = filterBanner(order);
            const hasAnyReview = order.detail_pemesanan.some(detail => {
                return (Array.isArray(detail.ulasan) && detail.ulasan.length > 0) || 
                    (detail.ulasan && typeof detail.ulasan === 'object' && Object.keys(detail.ulasan).length > 0);
            });

            // Format tanggal
            const formatDate = (dateString) => {
                if (!dateString) return "-";
                return new Date(dateString).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };

            // Header pesanan
            const headerHTML = `
                <div class="row border-bottom pb-2">
                    <div class="col-6">
                        <button id="kembali-button" class="btn btn-secondary mb-3">Kembali</button>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-0"><strong></strong> ${formatDate(order.tanggal_pemesanan)} <span class="${statusInfo.color}">${statusInfo.text}</span></p>
                    </div>
                </div>
            `;

            // Informasi pelanggan dan pengiriman
            const customerInfoHTML = `
                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-6 border-right pb-3">
                        <p class="mb-1"><strong>Nama:</strong> ${pelanggan.nama_pelanggan || "-"}</p>
                        <p class="mb-1"><strong>No. Telepon:</strong> 
                            <a href="${formatWhatsAppLink(pelanggan.telepon)|| "-"}" 
                                class="whatsapp-link" target="_blank"><i class="fab fa-whatsapp"></i> +${pelanggan.telepon}
                            </a>
                        </p>
                        <p class="mb-0"><strong>Alamat:</strong> ${order.alamat_pengiriman || "-"}</p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><strong>Metode Pembayaran:</strong> ${order.pembayaran?.metode_pembayaran || "-"}</p>
                        <p class="mb-1"><strong>Tanggal Pembayaran:</strong> ${formatDate(order.pembayaran?.tanggal_pembayaran)}</p>
                        <p class="mb-1"><strong>Kurir:</strong> ${order.pengiriman?.kurir || "-"}</p>
                        <p class="mb-1"><strong>Tanggal Dikirim:</strong> ${formatDate(order.pengiriman?.tanggal_pengiriman)}</p>
                        <p class="mb-0"><strong>Tanggal Diterima:</strong> ${formatDate(order.pengiriman?.tanggal_diterima)}</p>
                    </div>
                </div>
            `;

            // Daftar produk
            const productsHTML = `
                ${order.detail_pemesanan.map(detail => {
                    if (!Array.isArray(detail.ulasan)) {
                        detail.ulasan = Object.values(detail.ulasan);
                    }
                    const rating = detail.ulasan.length > 0 ? detail.ulasan[0].id_rating : 0;

                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-2">
                                        <img src="${detail.produk_variasi.gambar}" class="img-fluid rounded" alt="${detail.produk_variasi.nama_produk}">
                                    </div>
                                    <div class="col-6">
                                        <h6 class="mb-1">${detail.produk_variasi.nama_produk}</h6>
                                        <p class="text-muted small mb-1">${detail.produk_variasi.variasi || ""}</p>
                                        <div class="mt-2">
                                            ${rating > 0 ? '⭐'.repeat(rating) : '<span class="text-muted small">Belum ada penilaian</span>'}
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <p class="mb-1">${detail.jumlah} x Rp ${detail.produk_variasi.harga.toLocaleString()}</p>
                                        <p class="mb-0"><strong>Subtotal:</strong> Rp ${detail.sub_total_produk.toLocaleString()}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join("")}
            `;

            // Ringkasan pembayaran
            const summaryHTML = `
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row border-bottom pb-2 mb-2">
                            <div class="col-6"><p class="mb-0">Total Harga:</p></div>
                            <div class="col-6 text-end"><p class="mb-0">Rp ${order.total_harga?.toLocaleString() || "0"}</p></div>
                        </div>
                        <div class="row border-bottom pb-2 mb-2">
                            <div class="col-6"><p class="mb-0">Biaya Pengiriman:</p></div>
                            <div class="col-6 text-end"><p class="mb-0">Rp ${order.pengiriman?.biaya_pengiriman?.toLocaleString() || "0"}</p></div>
                        </div>
                        <div class="row border-bottom pb-2 mb-2">
                            <div class="col-6"><p class="mb-0">Potongan Voucher:</p></div>
                            <div class="col-6 text-end"><p class="mb-0">- Rp ${order.potongan_harga?.toLocaleString() || "0"}</p></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><p class="mb-0 fw-bold">Total Pembayaran:</p></div>
                            <div class="col-6 text-end"><p class="mb-0 fw-bold">Rp ${order.pembayaran?.total_pembayaran?.toLocaleString() || order.total_harga?.toLocaleString() || "0"}</p></div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        ${hasAnyReview ? `
                            <button type="button" class="btn btn-warning review-button-variation" data-id="${order.id_pemesanan}">Tampilkan Penilaian</button>
                        ` : ''}
                        ${(statusInfo.text === 'Dikirim' || statusInfo.text === 'Pesanan Diterima') ? `
                            <button type="button" class="btn btn-primary lihat-pembayaran" data-id="${order.id_pemesanan}">Lihat Pembayaran</button>
                        ` : ''}
                    </div>
                </div>
            `;

            // Gabungkan semua bagian
            const detailHTML = headerHTML + customerInfoHTML + productsHTML + summaryHTML;

            // Tampilkan detail pesanan
            $('#detail-pesanan').html(detailHTML);

            // Sembunyikan daftar pesanan dan tampilkan detail pesanan
            $('.data-pesanan').hide();
            $('#detail-pesanan-content').show();
        }
            
        $(document).on('click', '#kembali-button', function () {
            $('#detail-pesanan-content').hide();
            $('.data-pesanan').show();
        });

        function showReviewModal(orderId, detail) {            
            const modalBody = document.getElementById("reviewModalBody");
            
            // Create review content HTML
            let reviewsHTML = '';
            
            detail.forEach(detail => {
                let reviews = [];
                
                // Handle different ulasan formats
                if (Array.isArray(detail.ulasan)) {
                    reviews = detail.ulasan;
                } else if (detail.ulasan && typeof detail.ulasan === 'object') {
                    reviews = Object.values(detail.ulasan);
                }
                
                if (reviews.length > 0) {
                    reviews.forEach(review => {
                        const hasReply = review.balasan && review.balasan.length > 0;
                        const balasan = replyCache[review.id_ulasan];
                        
                        reviewsHTML += `
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <img src="${detail.produk_variasi.gambar}" class="img-fluid rounded" alt="${detail.produk_variasi.nama_produk}">
                                        </div>
                                        <div class="col-10">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">${detail.produk_variasi.nama_produk}</h6>
                                                    <p class="text-muted small mb-0">${detail.produk_variasi.variasi || ""}</p>
                                                </div>
                                                <div class="text-warning">${'⭐'.repeat(review.id_rating)}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="border-top pt-3">
                                        <div class="mb-2 d-flex justify-content-between">
                                            <strong>Ulasan Pelanggan:</strong>
                                            <small class="text-muted">ID Ulasan: ${review.id_ulasan}</small>
                                        </div>
                                        <p>${review.ulasan}</p>
                                    </div>
                                    
                                    <div class="border-top pt-3">
                                        ${
                                            balasan || hasReply
                                            ? `<div class="mb-2"><strong>Balasan:</strong></div>
                                            <p>${balasan || review.balasan[0].balasan}</p>`
                                            : `<form id="replyForm-${review.id_ulasan}" class="reply-form" data-id-ulasan="${review.id_ulasan}">
                                                <div class="mb-3">
                                                    <label for="replyText-${review.id_ulasan}" class="form-label">Balasan</label>
                                                    <textarea class="form-control" id="replyText-${review.id_ulasan}" rows="3" required></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary simpan-balasan">Simpan Balasan</button>
                                            </form>`
                                        }
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
            });
            
            if (reviewsHTML === '') {
                reviewsHTML = `
                    <div class="text-center p-4">
                        <p>Tidak ada penilaian untuk pesanan ini.</p>
                    </div>
                `;
            }
            
            modalBody.innerHTML = reviewsHTML;

            attachReplyFormListeners();
            // loadReply(id_ulasan);
    
            const reviewModal = new bootstrap.Modal(document.getElementById("reviewModal"));
            reviewModal.show();
        }
        
        function attachReplyFormListeners() {
            const replyForms = document.querySelectorAll('.reply-form');
            
            replyForms.forEach(form => {
                form.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    
                    const id_ulasan = this.getAttribute('data-id-ulasan');
                    await submitReply(event, id_ulasan);
                });
            });
        }
        
        // The async function for submitting replies
        async function submitReply(event, id_ulasan) {
            const replyText = document.getElementById(`replyText-${id_ulasan}`).value;
            
            if (!replyText.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Balasan tidak boleh kosong'
                });
                return;
            }

            const confirmResult = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda akan menyimpan balasan ini.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan!',
                cancelButtonText: 'Batal'
            });
            
            if (!confirmResult.isConfirmed) {
                return;
            }

            const submitButton = event.target.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
            
            try {
                const response = await fetch(`${getApiBaseUrl()}/api/ulasan/balasan/${id_ulasan}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getJwtToken()}`
                    },
                    body: JSON.stringify({
                        balasan: replyText
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                replyCache[id_ulasan] = replyText;

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Balasan berhasil disimpan!'
                });
                
                const formElement = document.getElementById(`replyForm-${id_ulasan}`);
                const replyContainer = formElement.parentElement;
                
                replyContainer.innerHTML = `
                    <div class="mb-2">
                        <strong>Balasan:</strong>
                    </div>
                    <p>${replyText}</p>
                `;
                
            } catch (error) {
                console.error('Error submitting reply:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menyimpan balasan. Silakan coba lagi.'
                });
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        }

        document.addEventListener("click", async function (event) {
            if (event.target.classList.contains("kirim-pesanan")) {
                const button = event.target;
                const pengirimanId = button.getAttribute("data-id");

                if (!pengirimanId) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "ID Pengiriman tidak valid!",
                    });
                    return;
                }

                // Tampilkan konfirmasi sebelum mengirim pesanan
                const confirmResult = await Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Pesanan ini akan dikirim dan tidak bisa dibatalkan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Kirim!",
                    cancelButtonText: "Batal"
                });

                // Jika user membatalkan, hentikan proses
                if (!confirmResult.isConfirmed) {
                    return;
                }

                try {
                    const response = await fetch(`${getApiBaseUrl()}/api/pengiriman/dikirim/${pengirimanId}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": `Bearer ${getJwtToken()}`
                        }
                    });

                    if (!response.ok) {
                        throw new Error("Gagal mengirim pesanan. Silakan coba lagi.");
                    }
                    await Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: "Pesanan berhasil dikirim.",
                    });
                    window.location.reload();

                } catch (error) {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: error.message,
                    });
                }
            }
        });

        document.addEventListener("click", async (event) => {
            if (event.target.classList.contains("batalkan-btn")) {
                const orderId = event.target.getAttribute("data-id");

                // Konfirmasi pembatalan pesanan menggunakan SweetAlert
                const confirmation = await Swal.fire({
                    title: "Batalkan Pesanan?",
                    text: "Apakah Anda yakin ingin membatalkan pesanan ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Ya, Batalkan",
                    cancelButtonText: "Batal"
                });

                if (confirmation.isConfirmed) {
                    try {
                        // Panggil API untuk membatalkan pesanan
                        const response = await fetch(`${getApiBaseUrl()}/api/pemesanan-dibatalkan/${orderId}`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "Authorization": `Bearer ${getJwtToken()}`
                            }
                        });

                        const result = await response.json();

                        if (response.ok) {
                            // Tampilkan SweetAlert sukses
                            await Swal.fire({
                                title: "Berhasil!",
                                text: result.message || "Pesanan berhasil dibatalkan.",
                                icon: "success",
                                confirmButtonText: "OK"
                            });

                            // Refresh halaman atau update UI
                            window.location.reload(); // Atau lakukan update UI secara manual
                        } else {
                            // Tampilkan SweetAlert error
                            await Swal.fire({
                                title: "Gagal!",
                                text: result.message || "Terjadi kesalahan saat membatalkan pesanan.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    } catch (error) {
                        // Tampilkan SweetAlert error jika fetch gagal
                        await Swal.fire({
                            title: "Gagal!",
                            text: "Terjadi kesalahan saat menghubungi server.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                }
            }
        });

        document.addEventListener("click", async (event) => {
            if (event.target.classList.contains("cencel-btn")) {
                const transactionId = event.target.getAttribute("data-id");

                // Konfirmasi pembatalan pesanan menggunakan SweetAlert
                const confirmation = await Swal.fire({
                    title: "Batalkan Pesanan?",
                    text: "Apakah Anda yakin ingin membatalkan pesanan ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Ya, Batalkan",
                    cancelButtonText: "Batal"
                });

                if (confirmation.isConfirmed) {
                    try {
                        // Panggil API untuk membatalkan transaksi Midtrans
                        const response = await fetch(`${getApiBaseUrl()}/api/cancel-transaction/${transactionId}`, {
                            method: "POST", // Sesuaikan dengan method route Anda (POST atau GET)
                            headers: {
                                "Content-Type": "application/json",
                                "Authorization": `Bearer ${getJwtToken()}`, // Jika API Anda memerlukan autentikasi
                                "X-CSRF-TOKEN": getCsrfToken() // Tambahkan CSRF token jika diperlukan
                            }
                        });

                        const result = await response.json();

                        if (response.ok) {
                            // Tampilkan SweetAlert sukses
                            await Swal.fire({
                                title: "Berhasil!",
                                text: result.message || "Pesanan berhasil dibatalkan.",
                                icon: "success",
                                confirmButtonText: "OK"
                            });

                            // Refresh halaman atau update UI
                            window.location.reload(); // Atau update UI tanpa reload
                        } else {
                            // Tampilkan SweetAlert error
                            await Swal.fire({
                                title: "Gagal!",
                                text: result.message || "Terjadi kesalahan saat membatalkan pesanan.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    } catch (error) {
                        // Tampilkan SweetAlert error jika fetch gagal
                        await Swal.fire({
                            title: "Gagal!",
                            text: "Terjadi kesalahan saat menghubungi server.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                }
            }
        });

        function setActiveTab(status) {
            tabs.forEach(tab => {
                if (tab.getAttribute("data-status") === status) {
                    tab.classList.add("active");
                } else {
                    tab.classList.remove("active");
                }
            });
            updateURL(status);
        }

        tabs.forEach(tab => {
            tab.addEventListener("click", function(event) {
                event.preventDefault();
                tabs.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                const status = this.getAttribute("data-status");
                updateURL(status);
                renderOrders(status);
            });
        });

        window.addEventListener("popstate", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get("status") || "Semua";
            tabs.forEach(tab => {
                tab.classList.remove("active");
                if (tab.getAttribute("data-status") === status) {
                    tab.classList.add("active");
                }
            });
            renderOrders(status);
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('lihat-pembayaran')) {
                const orderId = event.target.getAttribute('data-id');
                const pelanggan = {
                        nama_pelanggan: allOrders.nama_pelanggan,
                        telepon: allOrders.telepon
                    };  
                const order = allOrders.daftar_pemesanan.find((o) => o.id_pemesanan == orderId);
                openInvoice(pelanggan, order);
            }
        });

        function openInvoice(pelanggan, order){
            const invoiceWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
        
            // Generate invoice HTML
            const invoiceHTML = generateInvoiceHTML(pelanggan, order);
            
            // Write to the new window
            invoiceWindow.document.open();
            invoiceWindow.document.write(invoiceHTML);
            invoiceWindow.document.close();
            
            // Auto trigger print dialog when document is loaded
            invoiceWindow.document.addEventListener('DOMContentLoaded', function() {
                // Add a slight delay to ensure all resources are loaded
                setTimeout(() => {
                    invoiceWindow.focus();
                    // The print functionality is handled by a button in the invoice
                }, 1000);
            });
        }
        function generateInvoiceHTML(pelanggan, order) {
            // Get customer and format date
            const Pelanggan = pelanggan;
            const formatDate = (dateString) => {
                if (!dateString) return '-';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString('id-ID', options);
            };
            
            return `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
                <title>Jaya Studio #${order.id_pemesanan} ${Pelanggan.nama_pelanggan || "-"} </title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                    }
                    .invoice-header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .invoice-title {
                        font-size: 24px;
                        font-weight: bold;
                        margin-bottom: 5px;
                    }
                    .invoice-number {
                        font-size: 16px;
                        margin-bottom: 20px;
                    }
                    .company-info {
                        margin-bottom: 20px;
                    }
                    .logo {
                        max-height: 80px;
                        margin-bottom: 30px;
                    }
                    .table th {
                        background-color: #f8f9fa;
                    }
                    .footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 12px;
                        color: #6c757d;
                    }
                    @media print {
                        .no-print {
                            display: none;
                        }
                        body {
                            padding: 0;
                            margin: 0;
                        }
                        .container {
                            width: 100%;
                            max-width: 100%;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="row no-print mb-3">
                        <div class="col-12 text-end">
                            <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
                            <button class="btn btn-secondary ms-2" onclick="window.close()">Close</button>
                        </div>
                    </div>
                    
                    <div class="invoice-header">
                        <div class="logo text-center">
                            <img src="/frontend/img/jaya%20logo.png" alt="logo">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            
                        </div>
                        <div class="col-6 text-end">
                            <div>
                                <h5>Kepada:</h5>
                                <p>${Pelanggan.nama_pelanggan || "-"}<br>
                                ${order.alamat_pengiriman || "-"}<br>
                                Telepon: ${Pelanggan.telepon || "-"}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Metode Pembayaran:</strong> ${order.pembayaran?.metode_pembayaran || "-"}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Tanggal Pembayaran:</strong> ${formatDate(order.pembayaran?.tanggal_pembayaran)}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Kurir:</strong> ${order.pengiriman?.kurir || "-"}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Variasi</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${order.detail_pemesanan.map(detail => `
                                        <tr>
                                            <td>${detail.produk_variasi.nama_produk}</td>
                                            <td>${detail.produk_variasi.variasi || "-"}</td>
                                            <td class="text-end">Rp ${detail.produk_variasi.harga.toLocaleString()}</td>
                                            <td class="text-center">${detail.jumlah}</td>
                                            <td class="text-end">Rp ${detail.sub_total_produk.toLocaleString()}</td>
                                        </tr>
                                    `).join("")}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td>Total Harga</td>
                                        <td class="text-end">Rp ${order.total_harga?.toLocaleString() || "0"}</td>
                                    </tr>
                                    <tr>
                                        <td>Biaya Pengiriman</td>
                                        <td class="text-end">Rp ${order.pengiriman?.biaya_pengiriman?.toLocaleString() || "0"}</td>
                                    </tr>
                                    <tr>
                                        <td>Potongan Voucher</td>
                                        <td class="text-end">- Rp ${order.potongan_harga?.toLocaleString() || "0"}</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td>Total Pembayaran</td>
                                        <td class="text-end">Rp ${order.pembayaran?.total_pembayaran?.toLocaleString() || order.total_harga?.toLocaleString() || "0"}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mb-5">
                        <div class="company-info">
                            <p>Jaya Studio<br>
                            Jalan Nusantara No 1. Sidembunut, Cempaga, Bangli<br>
                            Email: jayastudio@gmail.com<br>
                            Telepon: 081238465833</p>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>Invoice ini sah dan diproses oleh komputer</p>
                        <p>© ${new Date().getFullYear()} Jaya Studio.</p>
                    </div>
                </div>
            </body>
            </html>
            `;
        }

        allOrders = await fetchOrders();
        renderOrders();
        renderPelanggan();
    });

</script>
@endpush