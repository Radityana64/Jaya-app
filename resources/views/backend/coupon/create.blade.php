@extends('backend.layouts.master')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <span id="form-title">Buat Voucher Baru</span>
                </div>
                <div class="card-body">
                    <form id="voucher-form" enctype="multipart/form-data">
                        <input type="hidden" id="voucher-id" name="id_voucher">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Kode Voucher <span class="text-danger">*</span></label>
                                    <input type="text" id="kode-voucher" name="kode_voucher" class="form-control" required>
                                    <small class="text-muted">Kode voucher harus unik</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Nama Voucher <span class="text-danger">*</span></label>
                                    <input type="text" id="nama-voucher" name="nama_voucher" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Diskon (%) <span class="text-danger">*</span></label>
                                    <input type="number" id="diskon" name="diskon" class="form-control" min="0" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Minimal Pembelian <span class="text-danger">*</span></label>
                                    <input type="number" id="min-pembelian" name="min_pembelian" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
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

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="memiliki-distribusi">
                                <label class="form-check-label" for="memiliki-distribusi">
                                    Distribusi Voucher
                                </label>
                            </div>
                        </div>

                        <div id="distribusi-container" class="mb-3" style="display:none;">
                            <label>Kriteria Distribusi</label>
                            <select id="kriteria-distribusi" class="form-control">
                                <option value="semua_pelanggan">Semua Pelanggan</option>
                                <!-- <option value="pelanggan_aktif">Pelanggan Aktif</option> -->
                                <option value="pelanggan_loyal">Pelanggan Loyal</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success" id="submit-voucher">Simpan</button>
                            <button type="button" class="btn btn-secondary" id="batal-edit">Batal</button>
                        </div>
                    </form>
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
            this.initEventListeners();
        }

        // Fungsi untuk mendapatkan CSRF Token
        getCsrfToken() {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            return csrfMeta ? csrfMeta.getAttribute('content') : '';
        }

        // Fungsi untuk mendapatkan JWT Token
        getJwtToken() {
            const tokenMeta = document.querySelector('meta[name="api-token"]');
            return tokenMeta ? tokenMeta.getAttribute('content') : '';
        }

        getApiBaseUrl(){
            return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
        }

        initEventListeners() {
            // Checkbox distribusi voucher
            document.getElementById('memiliki-distribusi').addEventListener('change', (e) => {
                const distribusiContainer = document.getElementById('distribusi-container');
                distribusiContainer.style.display = e.target.checked ? 'block' : 'none';
            });

            // Validasi tanggal
            const tanggalMulai = document.getElementById('tanggal-mulai');
            const tanggalAkhir = document.getElementById('tanggal-akhir');

            tanggalMulai.addEventListener('change', () => {
                tanggalAkhir.min = tanggalMulai.value;
            });

            tanggalAkhir.addEventListener('change', () => {
                tanggalMulai.max = tanggalAkhir.value;
            });

            // Submit form
            document.getElementById('voucher-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.simpanVoucher();
            });

            // Batal edit
            document.getElementById('batal-edit').addEventListener('click', () => {
                this.resetForm();
            });
        }

        simpanVoucher() {
            // Validasi input
            const kodeVoucher = document.getElementById('kode-voucher').value.trim();
            const namaVoucher = document.getElementById('nama-voucher').value.trim();
            const diskon = document.getElementById('diskon').value;
            const minPembelian = document.getElementById('min-pembelian').value;
            const tanggalMulai = document.getElementById('tanggal-mulai').value;
            const tanggalAkhir = document.getElementById('tanggal-akhir').value;
            const memilikiDistribusi = document.getElementById('memiliki-distribusi').checked;
            const kriteriaDistribusi = memilikiDistribusi 
                ? document.getElementById('kriteria-distribusi').value 
                : null;

            // Validasi input wajib
            if (!kodeVoucher || !namaVoucher || !diskon || !minPembelian || !tanggalMulai || !tanggalAkhir) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Semua field harus diisi'
                });
                return;
            }

            const form = document.getElementById('voucher-form');
            const formData = new FormData(form);

            // Kirim data ke backend
            fetch(`${this.getApiBaseUrl()}/api/vouchers`, {
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
                    // Jika ada distribusi, lakukan distribusi voucher
                    if (memilikiDistribusi) {
                        this.distribusiVoucher(data.data.id_voucher, kriteriaDistribusi);
                    } else {
                        this.tampilkanSukses();
                    }
                } else {
                    this.tampilkanError(data.message || 'Gagal menyimpan voucher');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.tampilkanError('Terjadi kesalahan saat menyimpan voucher');
            });
        }

        distribusiVoucher(voucherId, kriteria) {
            fetch(`${this.getApiBaseUrl()}/api/vouchers/distribusi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getJwtToken()}`,
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    id_voucher: voucherId,
                    kriteria_distribusi: kriteria
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.tampilkanSukses('Voucher berhasil disimpan dan didistribusikan');
                } else {
                    this.tampilkanError(data.message || 'Gagal mendistribusikan voucher');
                }
            })
            .catch(error => {
                console.error('Distribusi Error:', error);
                this.tampilkanError('Terjadi kesalahan saat mendistribusikan voucher');
            });
        }

        tampilkanSukses(pesan = 'Voucher berhasil disimpan') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: pesan,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                this.resetForm();
            });
        }

        tampilkanError(pesan) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: pesan,
            });
        }

        resetForm() {
            document.getElementById('voucher-form').reset();
            document.getElementById('distribusi-container').style.display = 'none';
            document.getElementById('memiliki-distribusi').checked = false;
        }
    }
    new VoucherManager();
});
</script>
@endpush