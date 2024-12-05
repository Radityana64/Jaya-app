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
                    pesanan.status_pemesanan === 'Proses_Pembayaran'
                );
            case 'dikemas':
                return pesananData.filter(pesanan => 
                    pesanan.status_pemesanan === 'Pesanan_Diterima'
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
        if (pesanan.status_pemesanan === 'Proses_Pembayaran') {
            return `
                <button class="btn btn-primary bayar-sekarang" 
                        data-id="${pesanan.id_pemesanan}"
                        data-snap-token="${pesanan.pembayaran.snap_token}">
                    Bayar Sekarang
                </button>
            `;
        }
        return '';
    }

    // Event listener untuk tombol bayar
    $(document).on('click', '.bayar-sekarang', function() {
        const snapToken = $(this).data('snap-token');
        
        // Pastikan Snap.js sudah dimuat
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
            <div class="pesanan-card mb-3">
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
        
        // Update active tab
        $('#pesananTabs .nav-link').removeClass('active');
        $(this).addClass('active');

        // Fetch pesanan sesuai status
        fetchPesanan(status);

        // Update URL
        history.pushState(null, '', `?status=${status}`);
    });

    // Muat pesanan default saat halaman pertama kali dimuat
    fetchPesanan('all');

    // Pastikan Snap.js dimuat
    function loadSnapJs() {
        if (!window.snap) {
            const script = document.createElement('script');
            script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
            script.async = true;
            script.onload = function() {
                console.log('Snap.js berhasil dimuat');
            }; document.head.appendChild(script);
        }
    }

    loadSnapJs();
});
</script>