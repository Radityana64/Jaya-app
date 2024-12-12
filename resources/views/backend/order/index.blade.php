@extends('backend.layouts.master')

@section('main-content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Manajemen Pesanan</h6>
        </div>
        <div class="card-body">
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

                <div id="pesanan-content" class="mt-3">
                    <!-- Konten pesanan akan dimuat di sini -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ulasan -->
<div class="modal fade" id="balasanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Balas Ulasan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea id="balasanText" class="form-control" rows="3" placeholder="Tulis balasan..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="submitBalasan">Kirim Balasan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pesanan-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .pesanan-header {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .pesanan-item img {
        border-radius: 8px;
    }
    .badge-belum-bayar { background-color: #ffc107; color: #212529; }
    .badge-dikemas { background-color: #17a2b8; color: white; }
    .badge-dikirim { background-color: #28a745; color: white; }
    .badge-selesai { background-color: #6c757d; color: white; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  function getJwtToken() {
        return $('meta[name="api-token"]').attr('content');
    }
    // Variabel global
    let pesananData = [];

    // Fungsi utilitas
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function renderRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += i <= rating ? '⭐' : '☆';
        }
        return stars;
    }

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
                    pesanan.status_pemesanan === 'Pesanan_Diterima'
                );
            default:
                return pesananData;
        }
    }

    function getBadgeClass(status) {
        const statusMap = {
            'Proses_Pembayaran': 'badge-belum-bayar',
            'Dikemas': 'badge-dikemas',
            'Dikirim': 'badge-dikirim',
            'Pesanan_Diterima': 'badge-info',
        };
        return `badge ${statusMap[status] || 'badge-secondary'}`;
    }

    function renderPesananActions(pesanan) {
      if(pesanan.pengiriman.status_pengiriman === 'Dikemas') {
        return `
                <button class="btn btn-success kirim-pesanan" 
                        data-id-pengiriman="${pesanan.pengiriman.id_pengiriman}">
                    Kirim Pesanan
                </button>
            `;
      }else{
          return '';
      }
    }

    function renderProductReviewActions(detail) {
    // Jika sudah ada ulasan
    if (detail.ulasan && detail.ulasan.length > 0) {
        const ulasan = detail.ulasan[0];
        
        // Jika sudah ada balasan
        if (ulasan.balasan && ulasan.balasan.length > 0) {
            return `
                <div class="review-info mt-2">
                    <p><strong>Ulasan Anda:</strong> ${ulasan.ulasan}</p>
                    <p><strong>Rating:</strong> ${renderRating(ulasan.id_rating)}</p>
                    <p><strong>Balasan Penjual:</strong> ${ulasan.balasan[0].balasan}</p>
                </div>
            `;
        } 
        // Jika belum ada balasan
        else {
            return `
                <div class="review-info mt-2">
                    <p><strong>Ulasan Anda:</strong> ${ulasan.ulasan}</p>
                    <p><strong>Rating:</strong> ${renderRating(ulasan.id_rating)}</p>
                    <button class="btn btn-info btn-sm balas-ulasan" 
                            data-id-ulasan="${ulasan.id_ulasan}">
                        Balas Ulasan
                    </button>
                </div>
            `;
        }
    }
    // Jika belum ada ulasan
    return `
        <div class="review-info mt-2">Belum Ada Ulasan</div>
    `;
}


function renderPesanan(pesananData, status = 'all') {
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
                <div>
                    <span class="mr-3">${new Date(pesanan.tanggal_pemesanan).toLocaleString()}</span>
                    <span class="font-weight-bold">Pemesan: ${pesanan.nama_pelanggan}</span>
                </div>
                <span class="badge ${getBadgeClass(pesanan.status_pemesanan)}">
                    ${pesanan.status_pemesanan.replace(/_/g, ' ')}
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
                            
                            ${renderProductReviewActions(detail)}
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

$(document).on('click', '.balas-ulasan', function() {
    const idUlasan = $(this).data('id-ulasan');
    
    // Tampilkan modal balasan ulasan
    $('#balasanModal').modal('show');
    
    // Set data untuk submit balasan
    $('#submitBalasan')
        .data('id-ulasan', idUlasan);
});


    // Fungsi untuk memuat data pesanan
    function loadPesanan(status = 'all') {
        const jwtToken = getJwtToken();
        $.ajax({
            url: 'http://127.0.0.1:8000/api/pemesanan/data/master',
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.status) {
                    pesananData = response.data;
                    const filteredPesanan = filterPesanan(response.data, status);
                    $('#pesanan-content').html(renderPesanan(filteredPesanan));
                } else {
                    $('#pesanan-content').html('<p>Tidak ada data pesanan.</p>');
                }
            },
            error: function() {
                $('#pesanan-content').html('<p>Terjadi kesalahan saat memuat data pesanan.</p>');
            }
        });
    }

    // Event listener untuk tab pesanan
    $('#pesananTabs a').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');
        loadPesanan(status);
        $(this).tab('show');
    });

    // Konfirmasi pengiriman
    $(document).on('click', '.kirim-pesanan', function() {
        const jwtToken=getJwtToken();
        const pesananId = $(this).data('id-pengiriman');

        $.ajax({
            url: `http://127.0.0.1:8000/api/pengiriman/dikirim/${pesananId}`,
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                alert('Pesanan berhasil Dikrim');
                loadPesanan(); // Refresh daftar pesanan
            },
            error: function(xhr) {
                alert('Gagal mengirim pesanan. ' + xhr.responseText);
            }
        });
    });

    // Kirim balasan ulasan
    $(document).on('click', '#submitBalasan', function() {
    const jwtToken = getJwtToken();
    const ulasanId = $(this).data('id-ulasan');
    const balasan = $('#balasanText').val();

    // Validasi input
    if (!balasan.trim()) {
        alert('Balasan tidak boleh kosong');
        return;
    }

    $.ajax({
        url: `http://127.0.0.1:8000/api/ulasan/balasan/${ulasanId}`,
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${jwtToken}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            balasan: balasan
        }),
        success: function(response) {
            $('#balasanModal').modal('hide');
            $('#balasanText').val(''); // Reset textarea
            alert('Balasan berhasil dikirim.');
            loadPesanan(); // Refresh data pesanan
        },
        error: function(xhr) {
            console.error("Error Details:", {
                status: xhr.status,
                responseText: xhr.responseText,
                readyState: xhr.readyState
            });
        }
    });
});

    // Load semua pesanan saat halaman dimuat
    loadPesanan();
});
</script>
@endpush