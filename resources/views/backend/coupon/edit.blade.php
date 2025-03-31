@extends('backend.layouts.master')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <span id="form-title">Edit Voucher</span>
                </div>
                <div class="card-body">
                    <form id="voucher-form" enctype="multipart/form-data">
                        <input type="hidden" id="voucher-id" name="id_voucher">
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label>Kode Voucher</label>
                                    <input type="text" id="kode-voucher" name="kode_voucher" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label>Nama Voucher</label>
                                    <input type="text" id="nama-voucher" name="nama_voucher" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Diskon (%) <span class="text-danger">*</span></label>
                                    <input type="number" id="diskon" name="diskon" class="form-control" min="0" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Minimal Pembelian <span class="text-danger">*</span></label>
                                    <input type="number" id="min-pembelian" name="min_pembelian" class="form-control" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select name="status" id="status-voucher" class="form-control">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Non Aktif</option>
                                        <option value="kadaluarsa">Kadaluarsa</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" id="tanggal-mulai" name="tanggal_mulai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Tanggal Berakhir <span class="text-danger">*</span></label>
                                    <input type="date" id="tanggal-akhir" name="tanggal_akhir" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success" id="submit-voucher">Simpan</button>
                            <button type="button" class="btn btn-secondary" id="batal-edit">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Pelanggan dengan Voucher</span>
                        <select id="filter-status" class="form-control w-25">
                            <option value="semua">Semua</option>
                            <option value="belum_terpakai">Belum Terpakai</option>
                            <option value="terpakai">Terpakai</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>ID Pelanggan</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="pelanggan-table-body">
                                <!-- Data pelanggan akan dimuat di sini -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    class VoucherManager {
        constructor() {
            this.voucherId = window.location.pathname.split('/').pop(); // Ganti dengan ID voucher yang sesuai
            this.voucherData = null;
            this.initEventListeners();
            this.fetchVoucherData();
        }

        getCsrfToken() {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            return csrfMeta ? csrfMeta.getAttribute('content') : '';
        }

        getJwtToken() {
            const tokenMeta = document.querySelector('meta[name="api-token"]');
            return tokenMeta ? tokenMeta.getAttribute('content') : '';
        }
        getApiBaseUrl(){
            return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
        }

        initEventListeners() {
            const tanggalMulai = document.getElementById('tanggal-mulai');
            const tanggalAkhir = document.getElementById('tanggal-akhir');

            tanggalMulai.addEventListener('change', () => {
                tanggalAkhir.min = tanggalMulai.value;
            });

            tanggalAkhir.addEventListener('change', () => {
                tanggalMulai.max = tanggalAkhir.value;
            });

            document.getElementById('voucher-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateVoucher();
            });

            document.getElementById('batal-edit').addEventListener('click', () => {
                this.loadVoucherData();
            });

            document.getElementById('filter-status').addEventListener('change', (e) => {
                this.loadPelangganTable(e.target.value);
            });
        }

        fetchVoucherData() {
            fetch(`${this.getApiBaseUrl()}/api/vouchers/${this.voucherId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.getJwtToken()}`,
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.voucherData = data;
                    this.loadVoucherData();
                } else {
                    this.tampilkanError('Gagal mengambil data voucher');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                this.tampilkanError('Terjadi kesalahan saat mengambil data');
            });
        }

        loadVoucherData() {
            if (!this.voucherData) return;

            const voucher = this.voucherData.voucher;
            document.getElementById('voucher-id').value = voucher.id_voucher;
            document.getElementById('kode-voucher').value = voucher.kode_voucher;
            document.getElementById('nama-voucher').value = voucher.nama_voucher;
            document.getElementById('diskon').value = voucher.diskon;
            document.getElementById('min-pembelian').value = voucher.min_pembelian;
            document.getElementById('status-voucher').value = voucher.status;
            document.getElementById('tanggal-mulai').value = voucher.tanggal_mulai.split(' ')[0];
            document.getElementById('tanggal-akhir').value = voucher.tanggal_akhir.split(' ')[0];

            this.loadPelangganTable('semua'); // Load semua data pelanggan saat pertama kali
        }

        loadPelangganTable(filter = 'semua') {
            const tbody = document.getElementById('pelanggan-table-body');
            tbody.innerHTML = '';

            let filteredPelanggan = this.voucherData.pelanggan_voucher;
            if (filter === 'belum_terpakai') {
                filteredPelanggan = filteredPelanggan.filter(p => p.status_voucher_pelanggan === 'belum_terpakai');
            } else if (filter === 'terpakai') {
                filteredPelanggan = filteredPelanggan.filter(p => p.status_voucher_pelanggan === 'terpakai');
            }

            filteredPelanggan.forEach((pelanggan, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${pelanggan.pelanggan.id_pelanggan}</td>
                    <td>${pelanggan.pelanggan.nama_pelanggan || 'Tidak Diketahui'}</td>
                    <td>${pelanggan.status_voucher_pelanggan}</td>
                `;
                tbody.appendChild(row);
            });

            if (filteredPelanggan.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data pelanggan</td></tr>';
            }
        }
       
        updateVoucher() {
            const formData = new FormData(document.getElementById('voucher-form'));
            formData.append('_method', 'PUT');

            fetch(`${this.getApiBaseUrl()}/api/vouchers/${this.voucherId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Authorization': `Bearer ${this.getJwtToken()}`,
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.voucherData.voucher = data.voucher || data.data;
                    this.tampilkanSukses();
                    this.loadVoucherData();
                    window.location.reload();
                } else {
                    this.tampilkanError(data.message || 'Gagal menyimpan perubahan');
                }
            })
            .catch(error => {
                console.error('Update Error:', error);
                this.tampilkanError('Terjadi kesalahan saat menyimpan perubahan');
            });
        }

        tampilkanSukses(pesan = 'Perubahan berhasil disimpan') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: pesan,
                timer: 1500,
                showConfirmButton: false
            });
        }

        tampilkanError(pesan) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: pesan,
            });
        }
    }

    new VoucherManager();
});
</script>
@endpush