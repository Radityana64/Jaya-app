@extends('backend.layouts.master')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <!-- Form Kategori -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <span id="form-title">Tambah Kategori Baru</span>
                </div>
                <div class="card-body">
                    <form id="kategori-form" enctype="multipart/form-data">
                        <input type="hidden" id="kategori-id" name="id_kategori">
                        
                        <div class="form-group mb-3">
                            <label>Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="nama-kategori" name="nama_kategori" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Gambar Kategori</label>
                            <input type="file" id="gambar-kategori" name="gambar_kategori" class="form-control" accept="image/*">
                            <div id="preview-gambar" class="mt-2">
                                <!-- Preview gambar akan dimuat di sini -->
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="memiliki-sub-kategori">
                                <label class="form-check-label" for="memiliki-sub-kategori">
                                    Memiliki Sub Kategori
                                </label>
                            </div>
                        </div>

                        <div id="sub-kategori-container" class="mb-3" style="display:none;">
                            <label>Sub Kategori</label>
                            <div id="sub-kategori-list">
                                <!-- Sub kategori akan dimuat di sini -->
                            </div>
                            <button type="button" id="tambah-sub-kategori" class="btn btn-sm btn-primary mt-2">
                                + Tambah Sub Kategori
                            </button>
                        </div>

                        <div class="form-group mb-3">
                            <label>Status</label>
                            <select name="status" id="status-kategori" class="form-control">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success" id="submit-kategori">Simpan</button>
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
document.addEventListener('DOMContentLoaded', function() {
    class KategoriManager {
        constructor() {
            this.initEventListeners();
        }

        initEventListeners() {
            // Checkbox sub kategori
            document.getElementById('memiliki-sub-kategori').addEventListener('change', (e) => {
                const subKategoriContainer = document.getElementById('sub-kategori-container');
                subKategoriContainer.style.display = e.target.checked ? 'block' : 'none';
            });

            // Tambah sub kategori
            document.getElementById('tambah-sub-kategori').addEventListener('click', () => {
                this.tambahSubKategori();
            });

            // Submit form
            document.getElementById('kategori-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.simpanKategori();
            });

            // Batal edit
            document.getElementById('batal-edit').addEventListener('click', () => {
                this.resetForm();
            });

            // Preview gambar
            document.getElementById('gambar-kategori').addEventListener('change', (e) => {
                this.previewGambar(e.target.files[0]);
            });
        }

        tambahSubKategori() {
            const subKategoriList = document.getElementById('sub-kategori-list');
            const subKategoriItem = document.createElement('div');
            subKategoriItem.classList.add('sub-kategori-item', 'input-group', 'mb-2');
            subKategoriItem.innerHTML = `
                <input type="text" class="form-control sub-kategori-input" placeholder="Nama Sub Kategori">
                <button type="button" class="btn btn-danger btn-sm hapus-sub-kategori">-</button>
            `;

            // Tambahkan event listener untuk hapus sub kategori
            subKategoriItem.querySelector('.hapus-sub-kategori').addEventListener('click', () => {
                subKategoriItem.remove();
            });

            subKategoriList.appendChild(subKategoriItem);
        }

        previewGambar(file) {
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

        simpanKategori() {
            // Validasi nama kategori
            const namaKategori = document.getElementById('nama-kategori').value.trim();
            const gambarKategori = document.getElementById('gambar-kategori').files[0];

            if (!namaKategori) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Nama Kategori harus diisi'
                });
                return;
            }

            if (!gambarKategori) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Gambar Kategori harus dipilih'
                });
                return;
            }

            const form = document.getElementById('kategori-form');
            const formData = new FormData(form);

            // Ubah status sesuai dengan enum di database
            const status = document.getElementById('status-kategori').value === 'active' ? 'aktif' : 'nonaktif';
            formData.set('status', status);

            // Cek apakah sub kategori dicentang
            const memilikiSubKategori = document.getElementById('memiliki-sub-kategori').checked;
            
            // Ambil sub kategori hanya jika checkbox sub kategori dicentang
            if (memilikiSubKategori) {
                const subKategoriInputs = document.querySelectorAll('.sub-kategori-input');
                const subKategoris = Array.from(subKategoriInputs)
                    .map(input => input.value)
                    .filter(val => val.trim() !== '');
                
                // Tambahkan sub kategori ke form data
                subKategoris.forEach((subKategori, index) => {
                    formData.append(`sub_kategori[${index}]`, subKategori);
                });
            }

            // Kirim data ke backend
            fetch('/api/kategori/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Kategori berhasil disimpan',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        this.resetForm();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal menyimpan kategori',
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
                    text: 'Terjadi kesalahan saat menyimpan kategori',
                });
            });
        }

        resetForm() {
            document.getElementById('kategori-form').reset();
            document.getElementById('preview-gambar').innerHTML = '';
            document.getElementById('sub-kategori-list').innerHTML = '';
            document.getElementById('kategori-id').value = '';
            document.getElementById('form-title').innerText = 'Tambah Kategori Baru';
            document.getElementById('sub-kategori-container').style.display = 'none';
            document.getElementById('memiliki-sub-kategori').checked = false;
        }
    }

    new KategoriManager();
});
</script>
@endpush