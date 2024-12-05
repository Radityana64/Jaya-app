<div class="container">
    <div class="pesanan-container">
        <ul class="nav nav-tabs" id="pesananTabs">
            <li class="nav-item">
                <a class="nav-link active" data-status="all" href="#semua">Semua Pesanan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="belum-bayar" href="#belum-bayar">Belum Bayar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="dikemas" href="#dikemas">Dikemas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="dikirim" href="#dikirim">Dikirim</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-status="selesai" href="#selesai">Selesai</a>
            </li>
        </ul>

        <div id="pesanan-content">
            <!-- Konten pesanan akan dimuat di sini -->
        </div>
    </div>
</div>

<!-- Modal untuk Ulasan -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Beri Ulasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="rating">Rating (1-5):</label>
                    <input type="number" id="rating" class="form-control" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label for="ulasan">Ulasan:</label>
                    <textarea id="ulasan" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="submitReview">Kirim Ulasan</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-1JTCrR9hP3kq-wie"></script>
<script>
$(document).ready(function() {
    // Fungsi untuk mengambil token
    function getJwtToken() {
        return $('meta[name="api-token"]').attr('content');
    }

    // Fungsi untuk memformat rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(angka);
    }

    // Fungsi untuk filter pesanan
    function filterPesanan(pesananData, status) {
        switch(status) {
            case 'belum-bayar':
                return pesananData.filter(pesanan => 
                    pesanan.pembayaran.status_pembayaran === 'Pending'
                );
            case 'dikemas':
                return pesananData.filter(pesanan => 
                    pesanan.pengiriman.status_pengiriman === 'Dikemas'
                );
            case 'dikirim':
                return pesananData.filter(pesanan => 
                    pesanan.pengiriman.status_pengiriman === 'Dikirim'
                );
            case 'selesai':
                return pesananData.filter(pesanan => 
                    pesanan.pengiriman.status_pengiriman === 'Diterima'
                );
            default:
                return pesananData;
        }
    }

    // Fungsi untuk render aksi pesanan
    function renderPesananActions(pesanan) {
        const hasReviewed = pesanan.ulasan && pesanan.ulasan.length > 0;

        let reviewHtml = '';
        if (hasReviewed) {
            const ulasan = pesanan.ulasan[0]; // Ambil ulasan pertama
            reviewHtml = `
            <div class="review">
                <p><strong>Ulasan:</strong> ${ulasan.ulasan}</p>
                <p><strong>Rating:</strong> ${renderRating(ulasan.id_rating)}</p>
            </div>
            `;
        }

        if (pesanan.pembayaran.status_pembayaran === 'Pending') {
            return `
                ${reviewHtml}
                <button class="btn btn-primary bayar-sekarang" 
                        data-id="${pesanan.id_pemesanan}"
                        data-snap-token="${pesanan.pembayaran.snap_token}">
                    Bayar Sekarang
                </button>
            `;
        } else if (pesanan.pengiriman.status_pengiriman === 'Dikirim') {
            return `
                ${reviewHtml}
                <button class="btn btn-success terima-pesanan" 
                        data-id-pengiriman="${pesanan.pengiriman.id_pengiriman}">
                    Pesanan Diterima
                </button>
            `;
        } else if (pesanan.pengiriman.status_pengiriman === 'Diterima') {
            if (!hasReviewed) {
                return `
                    ${reviewHtml}
                    <button class="btn btn-warning review-button" 
                            data-id-pemesanan="${pesanan.id_pemesanan}"
                            data-produk-variasi="${pesanan.detail_pemesanan[0].produk_variasi.id_produk_variasi}">
                        Nilai Produk
                    </button>
                `;
            } else {
                return `
                    ${reviewHtml}
                    <button class="btn btn-success beli-lagi" 
                            data-id-produk-variasi="${pesanan.detail_pemesanan[0].produk_variasi.id_produk_variasi}">
                        Beli Lagi
                    </button>
                `;
            }
        } else if (['Dikemas', 'Dikirim', 'Diterima'].includes(pesanan.pengiriman.status_pengiriman)) {
            return `
                ${reviewHtml}
                <button class="btn btn-info bukti-pembayaran" 
                        data-id="${pesanan.id_pemesanan}">
                    Bukti Pembayaran
                </button>
            `;
        }
        return '';
    }

    // Fungsi untuk menampilkan rating dalam bentuk bintang
    function renderRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += i <= rating ? '⭐' : '☆'; // Menggunakan emoji bintang
        }
        return stars;
    }

    $(document).on('click', '.terima-pesanan', function() {
        const idPengiriman = $(this).data('id-pengiriman');
        const jwtToken = getJwtToken();

        $.ajax({
            url: `http://127.0.0.1:8000/api/pengiriman/diterima/${idPengiriman}`,
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                alert('Pesanan berhasil diterima');
                fetchPesanan('all'); // Refresh daftar pesanan
            },
            error: function(xhr) {
                alert('Gagal menerima pesanan. ' + xhr.responseText);
            }
        });
    });


    // Event listener untuk tombol nilai
    $(document).on('click', '.review-button', function() {
        const idPemesanan = $(this).data('id-pemesanan');
        const idProdukVariasi = $(this).data('produk-variasi');
        showReviewModal(idPemesanan, idProdukVariasi);
    });

    // Event listener untuk tombol beli lagi
    $(document).on('click', '.beli-lagi', function() {
        const idProdukVariasi = $(this).data('id-produk-variasi');
        addToCart(idProdukVariasi);
    });

    // Fungsi untuk menampilkan modal ulasan
    function showReviewModal(idPemesanan, idProdukVariasi) {
        $('#reviewModal').modal('show');

        $('#submitReview').off('click').on('click', function() {
            const rating = $('#rating').val();
            const ulasan = $('#ulasan').val();
            submitReview(idPemesanan, idProdukVariasi, rating, ulasan);
        });
    }

    // Fungsi untuk mengirim ulasan
    function submitReview(idPemesanan, idProdukVariasi, rating, ulasan) {
        const jwtToken = getJwtToken();
        $.ajax({
            url: 'http://127.0.0.1:8000/api/ulasan/buat',
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
            success: function(response) {
                alert('Ulasan berhasil ditambahkan');
                $('#reviewModal').modal('hide');
                // Update tampilan pesanan dengan ulasan dan rating
                updatePesananWithReview(idPemesanan, rating, ulasan);
                fetchPesanan('all');
            },
            error: function(xhr) {
                alert('Gagal menambahkan ulasan. ' + xhr.responseText);
            }
        });
    }

    // Fungsi untuk memperbarui tampilan pesanan dengan ulasan
    function updatePesananWithReview(idPemesanan, rating, ulasan) {
        const pesananCard = $(`.pesanan-card[data-id="${idPemesanan}"]`);
        const reviewHtml = `
            <div class="review">
                <p><strong>Ulasan:</strong> ${ulasan}</p>
                <p><strong>Rating:</strong> ${renderRating(rating)}</p>
            </div>
        `;
        pesananCard.find('.pesanan-footer').prepend(reviewHtml);
    }

    // Fungsi untuk menambahkan produk ke keranjang
    function addToCart(idProdukVariasi) {
        const jwtToken = getJwtToken();
        $.ajax({
            url: 'http://127.0.0.1:8000/api/keranjang/tambah',
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
                alert('Produk berhasil ditambahkan ke keranjang');
                window.location.href = '/keranjang';
            },
            error: function(xhr) {
                alert('Gagal menambahkan produk ke keranjang. ' + xhr.responseText);
            }
        });
    }

    // Event listener untuk tombol bukti pembayaran
    $(document).on('click', '.bukti-pembayaran', function() {
        const idPemesanan = $(this).data('id');
        alert('Tampilkan bukti pembayaran untuk pemesanan ID: ' + idPemesanan);
    });

    // Fungsi untuk render pesanan
    function renderPesanan(pesananData) {
        if (pesananData.length === 0) {
            return `
                <div class="text-center py-4">
                    <p>Tidak ada pesanan.</p>
                </div>
            `;
        }

        return pesananData.map(pesanan => `
            <div class="pesanan-card mb-3" data-id="${pesanan.id_pemesanan}">
                <div class="pesanan-header d-flex justify-content-between">
                    <span>${pesanan.tanggal_pemesanan}</span>
                    <span class="badge ${getBadgeClass(pesanan.status_pemesanan)}">
                        ${pesanan.status_pemesanan}
                    </span>
                </div>
                <div class="pesanan-body">
                    ${pesanan.detail_pemesanan.map(detail => `
                        <div class="pesanan-item d-flex mb-2">
                            <img src="${detail.produk_variasi.gambar}" 
                                 class="mr-3" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="item-details">
                                <h5>${detail.produk_variasi.nama_produk}</h5>
                                <p>${detail.produk_variasi.variasi}</p>
                                <p>Qty: ${detail.jumlah}</p>
                                <p>Harga: Rp. ${formatRupiah(detail.sub_total_produk)}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="pesanan-footer">
                    <div class="total-harga">
                        Total: Rp. ${formatRupiah(pesanan.total_harga)}
                    </div>
                    ${renderPesananActions(pesanan)}
                </div>
            </div>
        `).join('');
    }

    // Fungsi untuk mendapatkan badge class
    function getBadgeClass(status) {
        const statusClasses = {
            'Proses_Pembayaran': 'badge-warning',
            'Pesanan_Diterima': 'badge-info',
            'Dikirim': 'badge-primary',
            'Diterima': 'badge-success'
        };
        return statusClasses[status] || 'badge-secondary';
    }

    // Fungsi utama untuk fetch pesanan
    function fetchPesanan(status = 'all') {
        const jwtToken = getJwtToken();

        $.ajax({
            url: 'http://127.0.0.1:8000/api/pemesanan/data',
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
 'Accept': 'application/json'
            },
            success: function(response) {
                if (response.status && response.data) {
                    const filteredPesanan = filterPesanan(response.data, status);
                    $('#pesanan-content').html(renderPesanan(filteredPesanan));
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

    // Event listener untuk tab
    $('#pesananTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');
        
        $('#pesananTabs .nav-link').removeClass('active');
        $(this).addClass('active');

        fetchPesanan(status);
        history.pushState(null, '', `?status=${status}`);
    });

    // Event listener untuk tombol bayar sekarang
    $(document).on('click', '.bayar-sekarang', function() {
        const snapToken = $(this).data('snap-token');
        if (window.snap) {
            // Nonaktifkan tombol selama proses pembayaran
            $(this).prop('disabled', true).text('Memproses...');

            // Jalankan pembayaran Snap
            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    alert('Pembayaran Berhasil');
                    window.location.reload();
                },
                onPending: function(result) {
                    alert('Pembayaran Pending');
                    window.location.reload();
                },
                onError: function(result) {
                    alert('Pembayaran Gagal');
                    window.location.reload();
                },
                onClose: function() {
                    // Aktifkan kembali tombol
                    $('.bayar-sekarang').prop('disabled', false).text('Bayar Sekarang');
                    console.log('Popup ditutup');
                }
            });
        } else {
            alert('Pembayaran tidak tersedia. Silakan coba lagi.');
        }
    });

    // Muat pesanan default saat halaman pertama kali dimuat
    fetchPesanan('all');
});
</script>