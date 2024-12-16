@extends('backend.layouts.master')

@section('title', 'E-SHOP || Banner Create')

@section('main-content')

<div class="container-fluid">
    <div class="row">
        <!-- Form Banner -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <span id="form-title">Tambah Banner Baru</span>
                </div>
                <div class="card-body">
                    <form id="banner-form" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" id="judul" name="judul" class="form-control" required>
                            <span id="judul-error" class="text-danger"></span>
                        </div>

                        <div class="form-group mb-3">
                            <label>Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control"></textarea>
                            <span id="deskripsi-error" class="text-danger"></span>
                        </div>

                        <div class="form-group mb-3">
                            <label>Gambar Banner <span class="text-danger">*</span></label>
                            <input type="file" id="gambar-banner" name="gambar_banner" class="form-control" accept="image/*" required>
                            <span id="gambar-error" class="text-danger"></span>
                            <div id="preview-gambar" class="mt-2">
                                <!-- Preview gambar akan dimuat di sini -->
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Tidak Aktif</option>
                            </select>
                            <span id="status-error" class="text-danger"></span>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success" id="submit-banner">Simpan</button>
                            <button type="button" class="btn btn-secondary" id="batal-edit">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function getJwtToken() {
            return $('meta[name="api-token"]').attr('content');
        }
document.addEventListener('DOMContentLoaded', function() {
    // Event listener untuk form submit
    document.getElementById('banner-form').addEventListener('submit', function(e) {
        e.preventDefault();
        simpanBanner();
    });

    // Event listener untuk preview gambar
    document.getElementById('gambar-banner').addEventListener('change', function(e) {
        previewGambar(e.target.files[0]);
    });

    function previewGambar(file) {
        const previewContainer = document.getElementById('preview-gambar');
        previewContainer.innerHTML = '';

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '200px';
                img.style.marginTop = '10px';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }

    function simpanBanner() {
        // Clear previous error messages
        document.getElementById('judul-error').textContent = '';
        document.getElementById('deskripsi-error').textContent = '';
        document.getElementById('gambar-error').textContent = '';
        document.getElementById('status-error').textContent = '';

        const jwtToken = getJwtToken();

        // Get form data
        const formData = new FormData();
        const judul = document.getElementById('judul').value.trim();
        const deskripsi = document.getElementById('deskripsi').value.trim();
        const gambarBanner = document.getElementById('gambar-banner').files[0];
        const status = document.getElementById('status').value;

        // Validasi
        let hasError = false;
        if (!judul) {
            document.getElementById('judul-error').textContent = 'Judul harus diisi.';
            hasError = true;
        }
        if (!gambarBanner) {
            document.getElementById('gambar-error').textContent = 'Gambar banner harus dipilih.';
            hasError = true;
        }
        if (!status) {
            document.getElementById('status-error').textContent = 'Status harus dipilih.';
            hasError = true;
        }

        if (hasError) return;

        // Append data to FormData
        formData.append('judul', judul);
        formData.append('deskripsi', deskripsi);
        formData.append('gambar_banner', gambarBanner);
        formData.append('status', status);

        // Kirim data ke backend
        fetch('/api/banner/create', {
            method: 'POST',
            body: formData,
            headers: {
            'Authorization': `Bearer ${jwtToken}`  // Menambahkan Bearer token
        }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Banner berhasil disimpan',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                  window.location.href = "/banner";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message || 'Gagal menyimpan banner',
                });

                // Jika ada error validasi, tampilkan detail
                if (data.errors) {
                    console.error('Validation Errors:', data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan!',
                text: 'Terjadi kesalahan saat menyimpan banner',
            });
        });
    }

    function resetForm() {
        document.getElementById('banner-form').reset();
        document.getElementById('preview-gambar').innerHTML = '';
        document.getElementById('form-title').innerText = 'Tambah Banner Baru';
    }
});
</script>
@endpush