@extends('backend.layouts.master')

@section('title', 'E-SHOP || Sales Report')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Penjualan</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="tanggalMulai" class="form-label">Tanggal Mulai:</label>
                            <input type="date" class="form-control" id="tanggalMulai" required>
                        </div>
                        <div class="col-md-3">
                            <label for="tanggalAkhir" class="form-label">Tanggal Akhir:</label>
                            <input type="date" class="form-control" id="tanggalAkhir" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button id="tampilkanLaporan" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                            <div class="btn-group ml-2 me-2">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter"></i> View
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" id="filterSemuaData">Semua Data</a></li>
                                    <li><a class="dropdown-item" href="#" id="filterKategori">Kategori</a></li>
                                </ul>
                            </div>
                            <a href="/laporan/grafik" class="btn btn-success ml-2">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </a>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="jumlahTransaksi">0</h3>
                                    <p>Jumlah Transaksi</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="totalPenjualan">Rp 0</h3>
                                    <p>Total Penjualan</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="totalLaba">Rp 0</h3>
                                    <p>Total Laba</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tables -->
                    <div class="table-responsive">
                        <!-- Wrapper for Category Table -->
                        <div id="kategoriTableWrapper" style="display: none;">
                            <table id="kategoriTable" class="table table-bordered table-striped display nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">No</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th class="text-center">Jumlah Terjual</th>
                                        <th class="text-center">Total Pendapatan</th>
                                        <th class="text-center">Laba</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Wrapper for Product Table -->
                        <div id="produkTableWrapper">
                            <table id="produkTable" class="table table-bordered table-striped display nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">No</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th>Produk</th>
                                        <th class="text-center">Jumlah Terjual</th>
                                        <th class="text-center">Total Pendapatan</th>
                                        <th class="text-center">Laba</th>
                                        <th class="text-center" style="width: 100px">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Variation Modal -->
                    <div class="modal fade" id="variasiModal" tabindex="-1" aria-labelledby="variasiModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="variasiModalLabel">Detail Variasi Produk</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <table id="variasiTable" class="table table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px">No</th>
                                                <th>Variasi</th>
                                                <th class="text-center">Jumlah Terjual</th>
                                                <th class="text-center">Total Pendapatan</th>
                                                <th class="text-center">Laba</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Filled by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<style>
    .small-box {
        border-radius: 10px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    }
    .small-box > .inner {
        padding: 10px;
    }
    .small-box h3 {
        font-size: 28px;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
        color: white;
    }
    .small-box p {
        color: white;
        font-size: 15px;
    }
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 15px;
        color: rgba(255,255,255,0.3);
        font-size: 60px;
    }
    .bg-info {
        background-color: #17a2b8 !important;
    }
    .bg-success {
        background-color: #28a745 !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
    }
    .detail-btn {
        color: #007bff;
        cursor: pointer;
    }
    .detail-btn:hover {
        color: #0056b3;
    }
    #kategoriTable, #produkTable {
        width: 100% !important;
    }
    #kategoriTable {
        display: none;
    }
    #kategoriTableWrapper, #produkTableWrapper {
        width: 98%;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script>
    function getJwtToken() {
        return $('meta[name="api-token"]').attr('content'); 
    }
    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }

    let kategoriTable, produkTable, variasiTable;
    let currentViewMode = 'semua';

    $(document).ready(function() {
        // Initialize DataTables
        kategoriTable = $('#kategoriTable').DataTable({
            responsive: true,
            order: [[5, 'desc']], // Default sort by profit desc
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });
        
        produkTable = $('#produkTable').DataTable({
            responsive: true,
            order: [[6, 'desc']], // Default sort by profit desc
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });
        
        variasiTable = $('#variasiTable').DataTable({
            responsive: true,
            order: [[4, 'desc']], // Default sort by profit desc
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });

        // By default, show the product table (all data)
        $('#produkTable').show();
        $('#kategoriTable').show();
    });
    
    document.getElementById('tampilkanLaporan').addEventListener('click', function () {
        const tanggalMulai = document.getElementById('tanggalMulai').value;
        const tanggalAkhir = document.getElementById('tanggalAkhir').value;

        if (!tanggalMulai || !tanggalAkhir) {
            Swal.fire({
                title: "Gagal mengambil data",
                text: "Tolong masukan tanggal mulai dan tanggal akhir dengan benar. Silakan coba lagi.",
                icon: "error",
                confirmButtonText: "OK"
            });
            return;
        }

        const requestData = {
            tanggal_mulai: tanggalMulai,
            tanggal_akhir: tanggalAkhir,
        };
        const jwtToken = getJwtToken();

        // Show loading indicator
        showLoading(true);

        fetch(`${getApiBaseUrl()}/api/laporan/penjualan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${jwtToken}`,
            },
            body: JSON.stringify(requestData),
        })
        .then(response => response.json())
        .then(data => {
            showLoading(false);
            if (data.status) {
                window.salesData = data.data; // Store data globally
                updateSummaryCards(data.data.ringkasan);
                displayData(currentViewMode);
            } else {
                Swal.fire({
                    title: "Gagal Mengambil Data",
                    text: data.message || "Pastikan Lagi",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Error:', error);
            Swal.fire({
                title: "Terjadi Kesalahan!",
                text: "Gagal mengambil data laporan. Silakan coba lagi.",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    });

    // Filter event listeners
    document.getElementById('filterSemuaData').addEventListener('click', function() {
        currentViewMode = 'semua';
        displayData('semua');
    });
    
    document.getElementById('filterKategori').addEventListener('click', function() {
        currentViewMode = 'kategori';
        displayData('kategori');
    });

    function showLoading(isLoading) {
        // Implement loading indicator if needed
    }

    function updateSummaryCards(ringkasan) {
        document.getElementById('jumlahTransaksi').textContent = ringkasan.jumlah_transaksi;
        document.getElementById('totalPenjualan').textContent = 'Rp ' + ringkasan.total_penjualan.toLocaleString();
        document.getElementById('totalLaba').textContent = 'Rp ' + ringkasan.total_laba.toLocaleString();
    }

    function displayData(viewMode) {
        if (!window.salesData) return;

        // Clear tables
        kategoriTable.clear();
        produkTable.clear();

        // Hide both wrappers by default
        $('#kategoriTableWrapper').hide();
        $('#produkTableWrapper').hide();

        if (viewMode === 'kategori') {
            // Fill category table with subcategories
            let rowIndex = 1;
            window.salesData.detail.forEach((kategori) => {
                kategori.kategori2.forEach((subkategori) => {
                    kategoriTable.row.add([
                        rowIndex++,
                        kategori.nama_kategori_1,
                        subkategori.nama_kategori_2,
                        `<span class="text-center d-block">${subkategori.jumlah_terjual}</span>`,
                        `<span class="text-end d-block">Rp ${subkategori.total_pendapatan.toLocaleString()}</span>`,
                        `<span class="text-end d-block">Rp ${subkategori.laba.toLocaleString()}</span>`
                    ]);
                });
            });

            // Draw the table and show the wrapper
            kategoriTable.draw();
            $('#kategoriTableWrapper').show();
        } else {
            // Fill product table
            let rowIndex = 1;
            window.salesData.detail.forEach((kategori) => {
                kategori.kategori2.forEach((subkategori) => {
                    subkategori.produk.forEach((produk) => {
                        produkTable.row.add([
                            rowIndex++,
                            kategori.nama_kategori_1,
                            subkategori.nama_kategori_2,
                            produk.nama_produk,
                            `<span class="text-center d-block">${produk.jumlah_terjual}</span>`,
                            `<span class="text-end d-block">Rp ${produk.total_pendapatan.toLocaleString()}</span>`,
                            `<span class="text-end d-block">Rp ${produk.laba.toLocaleString()}</span>`,
                            `<button class="btn btn-sm btn-info view-variations" data-bs-toggle="modal" data-bs-target="#variasiModal" data-product-id="${produk.id_produk}"><i class="fas fa-list"></i></button>`
                        ]);
                    });
                });
            });

            // Draw the table and show the wrapper
            produkTable.draw();
            $('#produkTableWrapper').show();
        }

        // Add event listeners after drawing tables
        $('.view-variations').on('click', function() {
            const productId = $(this).data('product-id');
            showProductVariations(productId);
        });
    }

    function showProductVariations(productId) {
        // Find the product and its variations
        variasiTable.clear();
        
        let variations = [];
        let productName = '';
        
        window.salesData.detail.forEach((kategori) => {
            kategori.kategori2.forEach((subkategori) => {
                subkategori.produk.forEach((produk) => {
                    if (produk.id_produk == productId) {
                        productName = produk.nama_produk;
                        variations = produk.produk_variasi;
                    }
                });
            });
        });
        
        // Update modal title
        document.getElementById('variasiModalLabel').textContent = `Detail Variasi: ${productName}`;
        
        // Fill variations table
        variations.forEach((variasi, index) => {
            // Create variation name from detail_variasi
            let variasiName = variasi.detail_variasi.map(d => `${d.opsi_variasi}`).join(' / ');
            
            variasiTable.row.add([
                index + 1,
                variasiName,
                `<span class="text-center d-block">${variasi.jumlah_terjual}</span>`,
                `<span class="text-end d-block">Rp ${variasi.total_pendapatan.toLocaleString()}</span>`,
                `<span class="text-end d-block">Rp ${variasi.laba.toLocaleString()}</span>`
            ]);
        });
        
        variasiTable.draw();
    }
</script>
@endpush