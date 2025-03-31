@extends('backend.layouts.master')

@section('main-content')
<div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <span id="form-title">Tambah Admin</span>
            </div>
            <div class="card-body">
                <form id="admin-form" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" id="nama-lengkap" name="nama_lengkap" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success" id="submit-admin">Simpan</button>
                        <button type="button" class="btn btn-secondary" id="batal-tambah">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    class AdminManager {
        constructor() {
            this.initEventListeners();
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
            document.getElementById('admin-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.createAdmin();
            });

            document.getElementById('batal-tambah').addEventListener('click', () => {
                this.resetForm();
            });
        }

        async createAdmin() {
            const form = document.getElementById('admin-form');
            const formData = new FormData(form);

            // Debugging: Log FormData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            try {
                const response = await fetch(`${this.getApiBaseUrl()}/api/data/admin/create`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Authorization': `Bearer ${this.getJwtToken()}`,
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Jika berhasil (status 200-299)
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Admin berhasil ditambahkan',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.reload();
                } else {
                    // Jika gagal (status di luar 200-299)
                    let errorMessage = data.message || 'Terjadi kesalahan';

                    if (response.status === 422 && data.errors) {
                        // Tangani error validasi (422 Unprocessable Entity)
                        const errorDetails = Object.entries(data.errors)
                            .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                            .join('\n');
                        errorMessage = `${data.message || 'Validasi gagal'}\n${errorDetails}`;
                    } else if (response.status === 409) {
                        // Tangani konflik (409 Conflict)
                        errorMessage = data.message || 'Data sudah ada di sistem.';
                    } else if (response.status === 500) {
                        errorMessage = 'Kesalahan server internal. Silakan coba lagi nanti.';
                    } else if (data.message) {
                        errorMessage = data.message; // Gunakan pesan dari API jika ada
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage,
                        whiteSpace: 'pre-line' // Untuk menampilkan baris baru (\n) dengan benar
                    });
                }
            } catch (error) {
                console.error('Create Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan jaringan atau server tidak merespons. Silakan coba lagi.',
                });
            }
        }

        resetForm() {
            document.getElementById('admin-form').reset();
        }
    }

    new AdminManager();
});
</script>
@endpush