<div class="pesanan-container">
    <!-- <div class="pesanan-render"> -->
    <form id="search-form" class="mb-3 d-flex">
        <input type="text" id="search_input" class="form-control" placeholder="Cari pesanan...">
        <button type="submit" id="search-button" class="btn btn-primary ml-2"><i class="ti-search"></i></button>
    </form>
    
    <div class="tabs">
        <ul class="nav nav-tabs" id="pesananTabs">
            <li class="nav-item">
                <a class="nav-link active" data-status="Semua" href="#semua">Semua Pesanan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="Belum Bayar" href="#belum-bayar">Belum Bayar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="Dikemas" href="#dikemas">Dikemas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="Dikirim" href="#dikirim">Dikirim</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="Selesai" href="#selesai">Selesai</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="Dibatalkan" href="#dibatalkan">Dibatalkan</a>
            </li>
        </ul>
    </div>

    <div id="pesanan-content" class="order-list">
        <!-- Konten pesanan akan dimuat di sini -->
    </div>
</div>
<div id="detail-pesanan-content" class="order-detail" style="display: none;">
    <div id="detail-pesanan"></div>
</div>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Ubah ke modal-xl untuk lebar yang lebih baik -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Ulasan Pesanan</h5> <!-- Ubah judul agar lebih fleksibel -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <!-- Konten akan diisi secara dinamis -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="submitReview" class="btn btn-primary">Kirim Ulasan</button>
            </div>
        </div>
    </div>
</div>

<style>
    .tabs {
        position: sticky;
        top: 64px; /* Sesuaikan dengan tinggi navbar */
        background: white;
        z-index: 900;
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
        color: #495057; /* Warna teks default */
        position: relative;
        text-align: center;
    }

    .nav-tabs .nav-link:hover,
    .nav-tabs .nav-link.active {
        color: #000000; /* Warna teks saat hover dan aktif */
    }

    .nav-tabs .nav-link.active {
        font-weight: bold;
        border-bottom: 2px solid #000000;
    }

    /* Underline untuk tab aktif */
    .nav-tabs .nav-link.active::after {
        content: '';
        display: block;
        width: 100%;
        height: 2px;
        background-color: #000000;
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
    .btn {
        height: 40px; /* Tinggi tombol seragam */
        text-align: center; /* Menengahkan teks dalam tombol */
        font-size: 12px; /* Ukuran font seragam */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px; /* Padding agar tombol tidak terlalu kecil */
        min-width: 100px; /* Lebar minimum agar tombol tidak terlalu kecil */
    }
    .border-atas-bawah {
        border-top: 1px solid #ccc; /* Warna dan ketebalan garis atas */
        border-bottom: 1px solid #ccc; /* Warna dan ketebalan garis bawah */
        padding-top: 10px; /* Tambahkan ruang di atas konten */
        padding-bottom: 10px; /* Tambahkan ruang di bawah konten */
    }
    /* Tambahkan padding pada isi modal */
    #reviewModal .modal-body {
        padding: 2rem; /* Atur padding sesuai kebutuhan, misalnya 2rem (32px) */
    }
    #reviewModal .modal-dialog {
        max-width: 90%; 
    }
    .star {
        font-size: 1.5rem;
        color: #ccc; /* Warna default */
        cursor: pointer;
    }

    .star.selected {
        color: gold; /* Warna saat dipilih */
    }
    .product-review {
        border-bottom: 1px solid #eee; /* Garis pemisah antar produk */
        padding-bottom: 1.5rem;
    }

    .product-review:last-child {
        border-bottom: none; /* Hilangkan garis di elemen terakhir */
    }
</style>
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-1JTCrR9hP3kq-wie"></script>
<script>
$(document).ready(function() {
    function getJwtToken() {
        return $('meta[name="api-token"]').attr('content');
    }
    function getApiBaseUrl() {
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }

    let dataPesanan = [];
    
    // Fungsi untuk memformat rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(angka);
    }
   
    function filterBanner(pesanan) {
        if (pesanan.pembayaran?.status_pembayaran === 'Pending') {
            return { text: 'Proses Pembayaran', color: 'text-warning' };
        }
        if (pesanan.pengiriman?.status_pengiriman === 'Dikemas' && pesanan.status_pemesanan !== 'Pesanan_Dibatalkan') {
            return { text: 'Dikemas', color: 'text-primary' };
        }
        if (pesanan.pengiriman?.status_pengiriman === 'Dikirim' && pesanan.status_pemesanan !== 'Pesanan_Dibatalkan') {
            return { text: 'Dikirim', color: 'text-info' };
        }
        if (pesanan.pengiriman?.status_pengiriman === 'Diterima') {
            return { text: 'Pesanan Diterima', color: 'text-success' };
        }
        if (pesanan.status_pemesanan === 'Pesanan_Dibatalkan') {
            return { text: 'Pesanan Dibatalkan', color: 'text-danger' };
        }
        return { text: 'Unknown', color: 'text-secondary' };
    }

    // Fungsi untuk render aksi pesanan
    function renderPesananActions(pesanan) {
        let reviewHtml = '';

        if (pesanan.pembayaran.status_pembayaran === 'Pending') {
            return `
                ${reviewHtml}
                <button class="btn bayar-sekarang" 
                        data-id="${pesanan.id_pemesanan}"
                        data-snap-token="${pesanan.pembayaran.snap_token}">
                    Bayar Sekarang
                </button>
            `;
        } else if (pesanan.pengiriman.status_pengiriman === 'Dikirim') {
            return `
                ${reviewHtml}
                <button class="btn terima-pesanan" 
                        data-id-pengiriman="${pesanan.pengiriman.id_pengiriman}"
                        data-id-pemesanan="${pesanan.id_pemesanan}">
                    Pesanan Diterima
                </button>
            `;
        } else if (pesanan.pengiriman.status_pengiriman === 'Diterima') {
            // Percabangan untuk status pengiriman 'Diterima'
            if (pesanan.detail_pemesanan && pesanan.detail_pemesanan.length > 0) {
                const detail = pesanan.detail_pemesanan[0]; // Ambil detail pertama

                // Jika ulasan kosong, tampilkan tombol "Nilai Produk"
                if (detail.ulasan && detail.ulasan.length === 0) {
                    return `
                        <button class="btn review-button-variation" 
                                data-id="${pesanan.id_pemesanan}">
                            Nilai Produk
                        </button>
                    `;
                } 
                // Jika ulasan sudah ada, tampilkan tombol "Beli Lagi"
                else {
                    return `
                        <button class="btn beli-lagi" 
                                data-id-produk-variasi="${detail.produk_variasi.id_produk_variasi}">
                            Beli Lagi
                        </button>
                    `;
                }
            }
        }

        return reviewHtml; // Kembalikan reviewHtml jika tidak ada kondisi yang terpenuhi
    }
    
    // Event listener untuk form pencarian
    document.getElementById("search-form").addEventListener("submit", function (event) {
            event.preventDefault(); // Mencegah reload halaman saat submit form
            const searchQuery = document.getElementById("search_input").value.toLowerCase().trim();
            searchOrders(searchQuery);
        });

    // Fungsi untuk mencari pesanan
    function searchOrders(query) {
        if (!query) {
            renderOrders(); // Jika input kosong, kembalikan tampilan semua pesanan
            return;
        }

        // Pindahkan tab ke "Semua"
        setActiveTab("Semua");

        const filteredOrders = dataPesanan.filter(order => {
            const idMatch = order.id_pemesanan.toString().includes(query);
            const productMatch = order.detail_pemesanan.some(detail =>
                detail.produk_variasi.nama_produk.toLowerCase().includes(query)
            );
            return idMatch || productMatch;
        });

        renderOrders("Semua", filteredOrders); // Render pesanan yang difilter
    }

    // Fungsi untuk mengaktifkan tab "Semua"
    function setActiveTab(tabName) {
        $('#pesananTabs .nav-link').removeClass('active');
        $(`#pesananTabs .nav-link[data-status="${tabName}"]`).addClass('active');
    }
    // Event listener untuk tab
    $('#pesananTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');

        $('#pesananTabs .nav-link').removeClass('active');
        $(this).addClass('active');

        renderOrders(status); // Render pesanan berdasarkan status
        history.pushState(null, '', `?status=${status}`);
    });

    // Fungsi untuk merender pesanan berdasarkan status
    function renderOrders(status = "Semua", filteredOrders = null) {
        const orderList = document.getElementById("pesanan-content"); // Pastikan elemen ini ada di HTML
        orderList.innerHTML = "";

        let ordersToRender = filteredOrders ? filteredOrders : dataPesanan.filter((order) => {
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

        orderList.innerHTML = ordersToRender.map(pesanan => {
            const status = filterBanner(pesanan); // Mendapatkan status yang benar
            return `
                <div class="order-item card p-3 mb-3 shadow-sm">
                    <div class="d-flex justify-content-between">
                        <p>Pesanan #${pesanan.id_pemesanan}</p>
                        <div class="d-flex"> 
                            <p class="text-muted mr-2">${pesanan.tanggal_pemesanan}</p>
                            <p><strong></strong> <span class="${status.color}">${status.text}</span></p>
                        </div>
                    </div>
                    <div class="order-products p-2 border-atas-bawah">
                        ${pesanan.detail_pemesanan.map(d => `
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2">
                                <div class="d-flex align-items-center">
                                    <img src="${d.produk_variasi.gambar}" alt="${d.produk_variasi.nama_produk}" class="rounded" width="50" height="50">
                                    <div class="px-2">
                                        <p class="mb-0">${d.produk_variasi.nama_produk}</p>
                                        <p class="text-muted small mb-1">${d.produk_variasi.variasi || ""}</p>
                                    </div>
                                </div>
                                <p class="text-end mb-0"><strong>Rp ${d.produk_variasi.harga.toLocaleString()} x ${d.jumlah}</strong></p>
                            </div>
                        `).join("")}
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <p class="mb-0"><strong>Rp ${pesanan.pembayaran.total_pembayaran.toLocaleString()}</strong></p>
                        <div class="d-flex"> 
                            <button class="btn btn-primary btn-sm show-details-btn" data-id="${pesanan.id_pemesanan}">Tampilkan Detail</button>
                            ${renderPesananActions(pesanan)}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Tambahkan ini di luar fungsi renderOrders atau showOrderDetails, idealnya di bagian inisialisasi kode Anda
    $(document).on('click', '.review-button-variation', function() {
        const orderId = $(this).data('id'); // Gunakan jQuery untuk ambil data-id
        const order = dataPesanan?.find((o) => o.id_pemesanan == orderId);
        const detail = order?.detail_pemesanan || [];

        if (order) {
            showReviewModal(orderId, detail);
        } else {
            console.error(`Detail pesanan dengan ID ${orderId} tidak ditemukan.`);
        }
    });

    $(document).on('click', '.show-details-btn', function () {
        const orderId = $(this).data('id');
        const order = dataPesanan.find(o => o.id_pemesanan == orderId); // Cari pesanan dari data global
        if (order) {
            showOrderDetails(order);
        } else {
            Swal.fire({
                title: "Pesanan Tidak Ditemukan!",
                text: "Pesanan yang Anda cari tidak tersedia.",
                icon: "error",
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    function showOrderDetails(order) {
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
                    <p class="mb-1"><strong>Nama:</strong> ${order.nama_pelanggan || "-"}</p>
                    <p class="mb-1"><strong>No. Telepon:</strong> ${order.telepon || "-"}</p>
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
                <div class="col-12 d-flex justify-content-end">
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
        $('.pesanan-container').hide();
        $('#detail-pesanan-content').show();
    }
        
    $(document).on('click', '#kembali-button', function () {
        $('#detail-pesanan-content').hide();
        $('.pesanan-container').show();
    });


    $(document).on('click', '.terima-pesanan', function() {
        const idPengiriman = $(this).data('id-pengiriman');
        const idPemesanan = $(this).data('id-pemesanan'); // Ambil id_pemesanan dari tombol
        const jwtToken = getJwtToken();

        $.ajax({
            url: `${getApiBaseUrl()}/api/pengiriman/diterima/${idPengiriman}`,
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                Swal.fire({
                    title: "Berhasil!",
                    text: "Pesanan berhasil diterima.",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });

                // Cari pesanan berdasarkan id_pemesanan dari dataPesanan
                const order = dataPesanan?.find((o) => o.id_pemesanan == idPemesanan);
                const detail = order?.detail_pemesanan || [];

                if (order && detail.length > 0) {
                    // Tampilkan modal ulasan dengan orderId dan detail
                    showReviewModal(idPemesanan, detail);
                } else {
                    console.error(`Detail pesanan dengan ID ${idPemesanan} tidak ditemukan.`);
                    fetchPesanan('all'); // Refresh daftar pesanan jika gagal
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: "Gagal!",
                    text: "Gagal menerima pesanan. " + (xhr.responseJSON?.message || xhr.responseText || "Terjadi kesalahan."),
                    icon: "error"
                });
            }
        });
    });
    
    function showReviewModal(orderId, detail) {
        console.log('detail', detail);

        const $modalBody = $('#reviewModalBody');
        $modalBody.empty();

        let hasForm = false; // Untuk menentukan apakah ada form ulasan

        detail.forEach((item, index) => {
            // Pastikan ulasan adalah array, jika tidak ubah ke array
            const ulasan = Array.isArray(item.ulasan) ? item.ulasan : (item.ulasan ? [item.ulasan] : []);

            let productHtml = '';

            if (ulasan.length > 0) {
                // Produk sudah memiliki ulasan
                const ulasanItem = ulasan[0]; // Ambil ulasan pertama
                const rating = ulasanItem.id_rating || ulasanItem.rating || 0;
                const teksUlasan = ulasanItem.ulasan || '-';

                // Tangani balasan sebagai array
                const balasanArray = Array.isArray(ulasanItem.balasan) ? ulasanItem.balasan : [];
                const teksBalasan = balasanArray.length > 0 
                    ? balasanArray.map(b => b.balasan).join('<br>') // Gabungkan semua balasan dengan baris baru
                    : ' ';

                productHtml = `
                    <div class="product-review mb-4" data-index="${index}">
                        <div class="row">
                            <div class="col-2">
                                <img src="${item.produk_variasi.gambar}" class="img-fluid rounded" alt="Gambar Produk">
                            </div>
                            <div class="col-10">
                                <h6 class="mb-1">${item.produk_variasi.nama_produk}</h6>
                                <p class="text-muted small mb-2">${item.produk_variasi.variasi || ''}</p>
                                <div class="mb-2">
                                    <span class="text-warning">${'★'.repeat(rating)}${'☆'.repeat(5 - rating)}</span>
                                </div>
                                <p><strong>Ulasan:</strong> ${teksUlasan}</p>
                                <p><strong>Balasan:</strong> ${teksBalasan}</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Produk belum memiliki ulasan, tampilkan form
                hasForm = true;
                productHtml = `
                    <div class="product-review mb-4" data-index="${index}">
                        <div class="row">
                            <div class="col-2">
                                <img src="${item.produk_variasi.gambar}" class="img-fluid rounded" alt="Gambar Produk">
                            </div>
                            <div class="col-10">
                                <h6 class="mb-1">${item.produk_variasi.nama_produk}</h6>
                                <p class="text-muted small mb-0">${item.produk_variasi.variasi || ''}</p>
                            </div>
                        </div>
                        <form class="ulasan-form mt-3" data-id-produk-variasi="${item.id_produk_variasi}">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="rating-stars">
                                    <span class="star" data-value="1">★</span>
                                    <span class="star" data-value="2">★</span>
                                    <span class="star" data-value="3">★</span>
                                    <span class="star" data-value="4">★</span>
                                    <span class="star" data-value="5">★</span>
                                </div>
                                <input type="hidden" class="rating-input" name="rating" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ulasan</label>
                                <textarea class="form-control" name="ulasan" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                `;
            }

            $modalBody.append(productHtml);
        });

        const $submitReviewBtn = $('#submitReview');
        if (hasForm) {
            $submitReviewBtn
                .data('id-pemesanan', orderId)
                .data('detail', detail)
                .off('click')
                .on('click', submitReviewHandler)
                .show();
        } else {
            $submitReviewBtn.hide();
        }

        $('#reviewModal').modal('show');
    }

    // Fungsi untuk menangani rating bintang (tetap sama)
    $(document).on('click', '.star', function () {
        const $form = $(this).closest('.ulasan-form');
        const value = $(this).data('value');
        $form.find('.rating-input').val(value);

        const $stars = $form.find('.star');
        $stars.removeClass('selected');
        $(this).prevAll('.star').addBack().addClass('selected');
    });

    // Fungsi untuk menangani submit ulasan (tetap sama)
    function submitReviewHandler() {
        const idPemesanan = $(this).data('id-pemesanan');
        const detail = $(this).data('detail');

        const reviews = [];
        let allValid = true;

        $('.ulasan-form').each(function () {
            const $form = $(this);
            const idProdukVariasi = $form.data('id-produk-variasi');
            const rating = $form.find('.rating-input').val();
            const ulasan = $form.find('textarea').val();

            if (!rating || rating == 0) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Harap berikan rating untuk semua produk.",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
                allValid = false;
                return false;
            }
            if (!ulasan.trim()) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Harap isi ulasan untuk semua produk.",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
                allValid = false;
                return false;
            }

            reviews.push({
                id_pemesanan: idPemesanan,
                id_produk_variasi: idProdukVariasi,
                rating: rating,
                ulasan: ulasan
            });
        });

        if (allValid) {
            let completedRequests = 0;
            reviews.forEach(review => {
                submitReview(
                    review.id_pemesanan,
                    review.id_produk_variasi,
                    review.rating,
                    review.ulasan,
                    () => {
                        completedRequests++;
                        if (completedRequests === reviews.length) {
                            Swal.fire({
                                title: "Sukses!",
                                text: "Semua ulasan berhasil ditambahkan.",
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#reviewModal').modal('hide');
                                window.location.reload();
                            });
                        }
                    }
                );
            });
        }
    }

    // Fungsi untuk mengirim ulasan ke server (tetap sama)
    function submitReview(idPemesanan, idProdukVariasi, rating, ulasan, callback) {
        const jwtToken = getJwtToken();
        $.ajax({
            url: `${getApiBaseUrl()}/api/ulasan/buat`,
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            data: JSON.stringify({
                id_pemesanan: idPemesanan,
                id_produk_variasi: idProdukVariasi,
                rating: rating,
                ulasan: ulasan
            }),
            contentType: 'application/json',
            success: function (response) {
                console.log('Ulasan berhasil untuk produk:', idProdukVariasi);
                if (callback) callback();
            },
            error: function (xhr) {
                Swal.fire({
                    title: "Gagal!",
                    text: `Gagal menambahkan ulasan untuk produk ${idProdukVariasi}: ${xhr.responseText}`,
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }

    // Event listener untuk tombol beli lagi
    $(document).on('click', '.beli-lagi', function() {
        const idProdukVariasi = $(this).data('id-produk-variasi');
        addToCart(idProdukVariasi);
    });

    // Fungsi untuk menambahkan produk ke keranjang
    function addToCart(idProdukVariasi) {
        const jwtToken = getJwtToken();
        $.ajax({
            url: `${getApiBaseUrl()}/api/keranjang/tambah`,
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            data: JSON.stringify({
                id_produk_variasi: idProdukVariasi,
                jumlah: 1
            }),
            contentType: 'application/json',
            success: function(response) {
                Swal.fire({
                    title: "Berhasil!",
                    text: "Produk berhasil ditambahkan ke keranjang.",
                    icon: "success",
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = '/keranjang';
                });
            },
            error: function(xhr) {
                Swal.fire({
                    title: "Gagal!",
                    text: `Gagal menambahkan produk ke keranjang: ${xhr.responseText}`,
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }

    // Fungsi utama untuk fetch pesanan
    function fetchPesanan() {
        const jwtToken = getJwtToken();

        $.ajax({
            url: `${getApiBaseUrl()}/api/pemesanan/data`,
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                const result = response.data;
                if (result) {
                    dataPesanan = result; // Simpan data pesanan ke variabel global
                    renderOrders("Semua"); // Render pesanan dengan status "Semua" setelah fetch
                } else {
                    console.error('Data pesanan tidak valid:', result);
                }
            },
            error: function(xhr) {
                $('#pesanan-content').html(`
                    <div class="alert alert-danger">
                        Gagal memuat pesanan. ${xhr.responseText}
                    </div>
                `);
            }
        });
    }

    // Event listener untuk tombol bayar sekarang
    $(document).on('click', '.bayar-sekarang', function() {
        const snapToken = $(this).data('snap-token');
        if (window.snap) {
            // Nonaktifkan tombol selama proses pembayaran
            $(this).prop('disabled', true).text('Memproses...');

            // Jalankan pembayaran Snap
            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    // alert('Pembayaran Berhasil');
                    window.location.reload();
                },
                onPending: function(result) {
                    // alert('Pembayaran Pending');
                    window.location.reload();
                },
                onError: function(result) {
                    // alert('Pembayaran Gagal');
                    window.location.reload();
                },
                onClose: function() {
                    // Aktifkan kembali tombol
                    $('.bayar-sekarang').prop('disabled', false).text('Bayar Sekarang');
                    console.log('Popup ditutup');
                }
            });
        } else {
            Swal.fire({
                title: "Pembayaran Tidak Tersedia!",
                text: "Silakan coba lagi.",
                icon: "error",
                showConfirmButton: false,
                timer: 2000
            });
        }
    });

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('lihat-pembayaran')) {
            const orderId = event.target.getAttribute('data-id');
            const order = dataPesanan.find((o) => o.id_pemesanan == orderId);
            openInvoice(order);
        }
    });

    function openInvoice(order){
        const invoiceWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
    
        // Generate invoice HTML
        const invoiceHTML = generateInvoiceHTML(order);
        
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
    function generateInvoiceHTML(order) {
        // Get customer and format date
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
            <title>Jaya Studio #${order.id_pemesanan} ${order.nama_pelanggan || "-"} </title>
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
                        <button class="btn btn-secondary ms-2" onclick="window.close()">Tutup</button>
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
                            <p>${order.nama_pelanggan || "-"}<br>
                            ${order.alamat_pengiriman || "-"}<br>
                            Telepon: ${order.telepon || "-"}</p>
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

    // Muat pesanan default saat halaman pertama kali dimuat
    fetchPesanan('all');
});
</script>