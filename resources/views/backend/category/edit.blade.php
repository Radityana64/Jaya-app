@extends('backend.layouts.master')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Kategori</h3>
                </div>
                <div class="card-body">
                    <form id="kategori-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="kategori-id" name="id_kategori">
                        
                        <div class="form-group mb-3">
                            <label>Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="nama-kategori" name="nama_kategori" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Gambar Kategori</label>
                            <input type="file" id="gambar-kategori" name="gambar_kategori" class="form-control" accept="image/*">
                            <div id="preview-gambar" class="mt-2"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Status</label>
                            <select name="status" id="status-kategori" class="form-control">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title">Sub Kategori</h5>
                                <button type="button" id="tambah-sub-kategori" class="btn btn-sm btn-primary">+ Tambah Sub Kategori</button>
                            </div>
                            <div class="card-body">
                                <div id="sub-kategori-list"></div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-success" id="submit-kategori">Update Kategori</button>
                            <a href="{{ route('index.kategori') }}" class="btn btn-secondary ml-2">Kembali</a>
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
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }

document.addEventListener('DOMContentLoaded', function() {
    class KategoriEditManager {
        constructor() {
            this.kategoriId = this.getKategoriIdFromUrl();
            this.initEventListeners();
            this.loadKategoriData();
        }

        getKategoriIdFromUrl() {
            const pathParts = window.location.pathname.split('/');
            return pathParts[pathParts.length - 1];
        }

        initEventListeners() {
            document.getElementById('tambah-sub-kategori').addEventListener('click', () => {
                this.tambahSubKategori();
            });

            document.getElementById('kategori-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateKategori();
            });

            document.getElementById('gambar-kategori').addEventListener('change', (e) => {
                this.previewGambar(e.target.files[0]);
            });
        }

        previewGambar(file) {
            const previewContainer = document.getElementById('preview-gambar');
            previewContainer.innerHTML = ''; 

            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.classList.add('img-thumbnail');
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        }

        loadKategoriData() {
            fetch(`/api/kategori/${this.kategoriId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal memuat data kategori');
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.status) {
                        this.populateForm(result.data);
                    } else {
                        this.showErrorMessage(result.message || 'Gagal memuat data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showErrorMessage(error.message || 'Terjadi kesalahan saat memuat data');
                });
        }

        populateForm(kategori) {
            document.getElementById('kategori-id').value = kategori.id_kategori;
            document.getElementById('nama-kategori').value = kategori.nama_kategori;
            document.getElementById('status-kategori').value = kategori.status;

            if (kategori.gambar_kategori) {
                const previewContainer = document.getElementById('preview-gambar');
                previewContainer.innerHTML = ''; 
                const img = document.createElement('img');
                img.src = kategori.gambar_kategori;
                img.style.maxWidth = '200px';
                img.classList.add('img-thumbnail');
                previewContainer.appendChild(img);
            }

            const subKategoriList = document.getElementById('sub-kategori-list');
            subKategoriList.innerHTML = ''; 
            
            if (kategori.sub_kategori && kategori.sub_kategori.length > 0) {
                kategori.sub_kategori.forEach(subKategori => {
                    this.tambahSubKategori(subKategori, true);
                });
            }
        }

        tambahSubKategori(subKategori = null, isExisting = false) {
            const subKategoriList = document.getElementById('sub-kategori-list');
            const subKategoriItem = document.createElement('div');
            subKategoriItem.classList.add('row', 'mb-2', 'sub-kategori-item');

            const hiddenIdInput = document.createElement('input');
            hiddenIdInput.type = 'hidden';
            hiddenIdInput.name = 'sub_kategori_id[]';
            hiddenIdInput.classList.add('sub-kategori-id');

            const namaInput = document.createElement('div');
            namaInput.classList.add('col-md-5');
            const namaInputElement = document.createElement('input');
            namaInputElement.type = 'text';
            namaInputElement.classList.add('form-control', 'sub-kategori-nama');
            namaInputElement.placeholder = 'Nama Sub Kategori';
            namaInputElement.name = 'sub_kategori_nama[]';
            namaInputElement.required = true;

            const statusInput = document.createElement('div');
            statusInput.classList.add('col-md-5');
            const statusSelect = document.createElement('select');
            statusSelect.classList.add('form-control', 'sub-kategori-status');
            statusSelect.name = 'sub_kategori_status[]';
            statusSelect.innerHTML = `
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Tidak Aktif</option>
            `;

            if (subKategori) {
                hiddenIdInput.value = subKategori.id_kategori;
                namaInputElement.value = subKategori.nama_kategori;
                statusSelect.value = subKategori.status;
            }

            const aksiInput = document.createElement('div');
            aksiInput.classList.add('col-md-2');
            
            if (!isExisting) {
                const hapusButton = document.createElement('button');
                hapusButton.type = 'button';
                hapusButton.classList.add('btn', 'btn-danger', 'btn-sm');
                hapusButton.textContent = 'Hapus';
                hapusButton.addEventListener('click', () => {
                    subKategoriItem.remove();
                });
                aksiInput.appendChild(hapusButton);
            }

            namaInput.appendChild(namaInputElement);
            statusInput.appendChild(statusSelect);

            subKategoriItem.appendChild(hiddenIdInput);
            subKategoriItem.appendChild(namaInput);
            subKategoriItem.appendChild(statusInput);
            subKategoriItem.appendChild(aksiInput);

            subKategoriList.appendChild(subKategoriItem);
        }

        updateKategori() {
            const namaKategori = document.getElementById('nama-kategori').value.trim();
            if (!namaKategori) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Nama Kategori harus diisi'
                });
                return;
            }

            // Validasi sub kategori
            const subKategoriNama = document.querySelectorAll('.sub-kategori-nama');
            for (let input of subKategoriNama) {
                if (!input.value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Nama Sub Kategori tidak boleh kosong'
                    });
                    return;
                }
            }

            // Struktur data yang sesuai dengan backend
            const payload = {
                nama_kategori: namaKategori,
                status: document.getElementById('status-kategori').value,
                sub_kategori: [],
                new_sub_kategori: []
            };

            // Proses sub kategori yang sudah ada
            const subKategoriItems = document.querySelectorAll('.sub-kategori-item');
            subKategoriItems.forEach(item => {
                const subKategoriId = item.querySelector('.sub-kategori-id').value;
                const namaInput = item.querySelector('.sub-kategori-nama').value;
                const statusSelect = item.querySelector('.sub-kategori-status').value;

                if (subKategoriId) {
                    // Sub kategori yang sudah ada
                    payload.sub_kategori.push({
                        id: subKategoriId,
                        nama: namaInput,
                        status: statusSelect
                    });
                } else {
                    // Sub kategori baru
                    payload.new_sub_kategori.push(namaInput);
                }
            });

            // Siapkan FormData untuk gambar
            const formData = new FormData();
            formData.append('nama_kategori', payload.nama_kategori);
            formData.append('status', payload.status);
            
            // Tambahkan gambar jika ada
            const gambarInput = document.getElementById('gambar-kategori');
            if (gambarInput.files.length > 0) {
                formData.append('gambar_kategori', gambarInput.files[0]);
            }

            // Tambahkan sub kategori ke formData
            payload.sub_kategori.forEach((subKategori, index) => {
                formData.append(`sub_kategori[${index}][id]`, subKategori.id);
                formData.append(`sub_kategori[${index}][nama]`, subKategori.nama);
                formData.append(`sub_kategori[${index}][status]`, subKategori.status);
            });

            // Tambahkan sub kategori baru
            payload.new_sub_kategori.forEach((nama, index) => {
                formData.append(`new_sub_kategori[${index}]`, nama);
            });
            formData.append('_method', 'PUT');

            // Kirim data ke backend
            fetch(`/api/kategori/update/${this.kategoriId}`, {
                method: 'POST', // Ganti ke POST jika backend tidak mendukung PUT dengan FormData
                body: formData,
                headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${getJwtToken()}`
            }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memperbarui kategori');
                }
                return response.json();
            })
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Kategori berhasil diperbarui',
                        timer: 1500,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.href = "{{ route('index.kategori') }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal memperbarui kategori',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: error.message || 'Terjadi kesalahan saat memperbarui kategori',
                });
            });
        }

        showErrorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: message
            });
        }
    }

    // Initialize manager
    new KategoriEditManager();
});
</script>
@endpush