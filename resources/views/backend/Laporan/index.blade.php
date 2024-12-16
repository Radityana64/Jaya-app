@extends('backend.layouts.master')

@section('title', 'E-SHOP || Banner Edit')

@section('main-content')
<div class="container report-container">
        <h1 class="text-center mb-4">Laporan Penjualan</h1>
        
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tanggalMulai" class="form-label">Tanggal Mulai:</label>
                    <input type="date" class="form-control" id="tanggalMulai" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tanggalAkhir" class="form-label">Tanggal Akhir:</label>
                    <input type="date" class="form-control" id="tanggalAkhir" required>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button id="tampilkanLaporan" class="btn btn-primary me-2">Tampilkan Laporan</button>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Filter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item" href="#" id="filterSemuaData">Semua Data</a></li>
                            <li><a class="dropdown-item" href="#" id="filterKategori">Tampilkan Kategori</a></li>
                            <li><a class="dropdown-item" href="#" id="filterProduk">Tampilkan Produk</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="ringkasanSection" class="mb-4">
            <h2>Ringkasan Laporan</h2>
            <div id="ringkasanContent" class="row"></div>
        </div>

        <div id="output" class="mt-4"></div>
    </div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .report-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .filter-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .category-item, .product-item {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #f1f3f5;
            border-radius: 8px;
        }
        .variation-item {
            background-color: #e9ecef;
            margin-top: 10px;
            padding: 10px;
            border-radius: 6px;
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        function getJwtToken() {
            return $('meta[name="api-token"]').attr('content');
        }
        document.getElementById('tampilkanLaporan').addEventListener('click', function () {
            const tanggalMulai = document.getElementById('tanggalMulai').value;
            const tanggalAkhir = document.getElementById('tanggalAkhir').value;

            const requestData = {
                tanggal_mulai: tanggalMulai,
                tanggal_akhir: tanggalAkhir,
            };
            const jwtToken = getJwtToken();

            fetch('http://127.0.0.1:8000/api/laporan/penjualan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${jwtToken}`,

                },
                body: JSON.stringify(requestData),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    displayData(data.data, 'all');
                } else {
                    alert('Gagal mengambil data laporan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data');
            });
        });

        // Filter event listeners
        document.getElementById('filterSemuaData').addEventListener('click', () => displayData(window.salesData, 'all'));
        document.getElementById('filterKategori').addEventListener('click', () => displayData(window.salesData, 'kategori'));
        document.getElementById('filterProduk').addEventListener('click', () => displayData(window.salesData, 'produk'));

        function displayData(data, filterType) {
            window.salesData = data; // Store data globally for filtering
            const output = document.getElementById('output');
            const ringkasanContent = document.getElementById('ringkasanContent');
            output.innerHTML = '';
            ringkasanContent.innerHTML = '';

            // Ringkasan Section
            const ringkasanCol1 = `
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Jumlah Transaksi</h5>
                            <p class="card-text">${data.ringkasan.jumlah_transaksi}</p>
                        </div>
                    </div>
                </div>
            `;
            const ringkasanCol2 = `
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Penjualan</h5>
                            <p class="card-text">Rp ${data.ringkasan.total_penjualan.toLocaleString()}</p>
                        </div>
                    </div>
                </div>
            `;
            const ringkasanCol3 = `
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Laba</h5>
                            <p class="card-text">Rp ${data.ringkasan.total_laba.toLocaleString()}</p>
                        </div>
                    </div>
                </div>
            `;
            ringkasanContent.innerHTML = ringkasanCol1 + ringkasanCol2 + ringkasanCol3;

            // Periode
            const periodeHeader = document.createElement('h3');
            periodeHeader.textContent = `Periode: ${data.periode.mulai} - ${data.periode.akhir}`;
            periodeHeader.className = 'text-center mb-4';
            output.appendChild(periodeHeader);

            data.detail.forEach(kategori => {
                if (filterType === 'all' || filterType === 'kategori') {
                    const kategoriDiv = document.createElement('div');
                    kategoriDiv.className = 'category-item';
                    kategoriDiv.innerHTML = `
                        <h4 class="text-primary">Kategori: ${kategori.nama_kategori_1}</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <p>Jumlah Terjual: ${kategori.jumlah_terjual}</p>
                            </div>
                            <div class="col-md-4">
                                <p>Total Pendapatan: Rp ${kategori.total_pendapatan.toLocaleString()}</p>
                            </div>
                            <div class="col-md-4">
                                <p>Laba: Rp ${kategori.laba.toLocaleString()}</p>
                            </div>
                        </div>
                    `;
                    output.appendChild(kategoriDiv);
                }

                if (filterType === 'all' || filterType === 'produk') {
                    kategori.kategori2.forEach(subkategori => {
                        subkategori.produk.forEach(produk => {
                            const produkDiv = document.createElement('div');
                            produkDiv.className = 'product-item';
                            produkDiv.innerHTML = `
                                <h5 class="text-success">Produk: ${produk.nama_produk}</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p>Jumlah Terjual: ${produk.jumlah_terjual}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p>Total Pendapatan: Rp ${produk.total_pendapatan.toLocaleString()}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p>Laba: Rp ${produk.laba.toLocaleString()}</p>
                                    </div>
                                </div>
                            `;

                            // Produk Variasi
                            produk.produk_variasi.forEach(variasi => {
                                const variasiDiv = document.createElement('div');
                                variasiDiv.className = 'variation-item';
                                variasiDiv.innerHTML = `
                                    <p><strong>Variasi ID:</strong> ${variasi.id_produk_variasi}</p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p>Jumlah Terjual: ${variasi.jumlah_terjual}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p>Total Pendapatan: Rp ${variasi.total_pendapatan.toLocaleString()}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p>Laba: Rp ${variasi.laba.toLocaleString()}</p>
                                        </div>
                                    </div>
                                    <p><strong>Detail Variasi:</strong></p>
                                `;

                                variasi.detail_variasi.forEach(detail => {
                                    const detailP = document.createElement('p');
                                    detailP.textContent = `${detail.tipe_variasi}: ${detail.opsi_variasi}`;
                                    variasiDiv.appendChild(detailP);
                                });

                                produkDiv.appendChild(variasiDiv);
                            });

                            output.appendChild(produkDiv);
                        });
                    });
                }
            });
        }
    </script>
@endpush
