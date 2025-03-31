@extends('backend.layouts.master')

@section('main-content')
<div class="containe m-5">
    <h2>Edit Produk</h2>
    <form id="editProductForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="product_id" name="product_id">
        <div class="form-group position-relative">
          <label for="cat_id">Category <span class="text-danger">*</span></label>
          <div class="input-group">
              <input type="text" id="cat_id" name="kategori" 
                    class="form-control category-autocomplete" 
                    placeholder="Search or select category" 
                    autocomplete="off" required>
              <input type="hidden" id="selected_kategori_id" name="id_kategori">
          </div>
          
          <div class="categories-container bg-white border shadow-sm mt-2" id="category-dropdown" style="display:none;">
            <div class="category-container">
                <div class="main-categories">
                    <!-- Main categories will be dynamically populated here -->
                </div>
                <div class="subcategories-panel" id="subcategories-panel">
                    <!-- Subcategories will be dynamically populated here -->
                </div>
            </div>
        </div>
      </div>

        <div class="form-group">
            <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
            <input id="nama_produk" type="text" name="nama_produk" placeholder="Masukkan nama produk" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Singkat <span class="text-danger">*</span></label>
            <input id="deskripsi" type="text" name="deskripsi" placeholder="Masukkan deskripsi singkat" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="detail_description">Deskripsi Detail</label>
            <div id="detail_description"></div>
            <input type="hidden" name="detail_produk[deskripsi_detail]" id="detail_produk_deskripsi_detail">
        </div>

        <div class="form-group">
            <label for="url_video">URL Video</label>
            <input id="url_video" type="url" name="detail_produk[url_video]" placeholder="Masukkan URL video" class="form-control">
        </div>

        <div class="form-group">
            <label for="gambar_produk">Gambar Produk</label>
            <input id="gambar_produk" type="file" name="gambar_produk[]" multiple class="form-control" accept="image/jpeg,image/png,image/jpg">
            <div id="existing-product-images" class="mt-2 d-flex flex-wrap"></div>
        </div>

        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Non-Aktif</option>
            </select>
        </div>

        <div class="form-group">
            <label>Variasi Produk</label>
            <div id="existing-variations-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tipe & Opsi Variasi</th>
                        <th>Stok</th>
                        <th>Berat</th>
                        <th>HPP</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Gambar</th>
                    </tr>
                </thead>
                <tbody id="variations-table-body">
                    <!-- Variasi akan dirender di sini -->
                </tbody>
            </table>
            </div>
            <button type="button" class="btn btn-primary" id="addVariationButton">
                Tambah Variasi Baru
            </button>
        </div>
        

        <div class="modal fade" id="addVariationModal" tabindex="-1" aria-labelledby="addVariationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addVariationModalLabel">Tambah Variasi Baru</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">  

                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-3">Simpan Perubahan</button>
    </form>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css">
<style>
    .category-container {
        display: flex; /* Menggunakan Flexbox */
        align-items: flex-start; /* Menyelaraskan item ke atas */
        gap: 16px; /* Jarak antara main-categories dan subcategories-panel */
    }

    .main-categories {
        width: 250px;
        border-right: 1px solid #e0e0e0;
        padding: 10px;
        max-height: 400px;
        overflow-y: auto;
    }

    #subcategories-panel {
        flex: 1; /* Mengisi ruang yang tersedia */
        max-width: 200px; /* Lebar maksimal untuk subcategories-panel */
        padding: 8px;
        overflow-y: auto; /* Scroll jika konten terlalu panjang */
    }

    .category-item, .subcategory-item {
        padding: 10px;
        transition: all 0.3s ease;
    }

    .category-item:hover, 
    .subcategory-item:hover {
        background-color: #f0f0f0;
        cursor: pointer;
    }

    .main-categories .category-item.active {
        background-color: #e0e0e0;
    }
    .is-invalid {
    border-color: #dc3545 !important;
    }
    .existing-variation-item {
        border: 1px solid #ddd;
        margin-bottom: 10px;
        padding: 10px;
    }
    .existing-variation-image {
        max-width: 100px;
        max-height: 100px;
        margin-right: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }
    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }

    document.addEventListener('DOMContentLoaded', function() {
    let temporaryOptions = [];
    let productData = null;
    let selectedVariations = [];
    class ProductEditManager {
        constructor() {
            this.productId = this.getProductIdFromUrl();
            this.categories = [];
            this.allCategories = [];

            this.variations = [];
            this.productVariations = [];
            this.newVariations = [];

            this.maxImages = 5; //maksimal gambar produk
            this.productImages = []; //menyimpan data gambar produk 
            
            this.formElement = document.getElementById('editProductForm');

            this.modal = document.getElementById('addVariationModal'); // Inisialisasi di sini (atau di deklarasi properti)
            this.modalBody = this.modal.querySelector('.modal-body');
            this.addNewVariation = this.addNewVariation.bind(this);
            this.initializeComponents();
        }

        // Utility Methods
        getProductIdFromUrl() {
            const pathParts = window.location.pathname.split('/');
            return pathParts[pathParts.length - 1];
        }

        async fetchData(url) {
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error('Gagal memuat data');
                return await response.json();
            } catch (error) {
                this.showErrorMessage(error.message);
                console.error(error);
                return null;
            }
        }

        // Initialization Methods
        initializeComponents() {
            this.initSummernote();
            this.initEventListeners();
            this.loadProductData();
            this.setupCategoryDropdown();
        }

        initSummernote() {
            $('#detail_description').summernote({
                height: 200,
                callbacks: {
                    onChange: (contents) => {
                        $('#detail_produk_deskripsi_detail').val(contents);
                    }
                }
            });
        }

        initEventListeners() {
            this.formElement.addEventListener('submit', this.handleFormSubmit.bind(this));
            
            const imageInput = document.getElementById('gambar_produk'); // Assume this is your file input ID
            if (imageInput) {
                imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
            }
            const addVariationButton = document.getElementById('addVariationButton');
            console.log("addVariationButton:", addVariationButton); // Cek elemen tombol
            console.log("typeof addVariationButton:", typeof addVariationButton); // Cek tipenya
            addVariationButton.addEventListener('click', () => this.addNewVariation()); // Gunakan arrow function
        }
        // Data Loading Methods
        async loadProductData() {
            try {
                const [categoriesResponse, productResponse] = await Promise.all([
                    this.fetchData(`${getApiBaseUrl()}/api/kategori`),
                    this.fetchData(`${getApiBaseUrl()}/api/produk/${this.productId}`)
                ]);

                if (categoriesResponse?.status && productResponse?.status === 'success') {
                    this.categories = categoriesResponse.data;
                    productData = productResponse.data;
                    this.populateForm(productData);
                    this.renderCategories();
                } else {
                    throw new Error('Gagal memuat data produk atau kategori');
                }
            } catch (error) {
                this.showErrorMessage(error.message);
            }
        }

        renderCategories() {
            const mainCategoriesContainer = document.querySelector('.main-categories');
            mainCategoriesContainer.innerHTML = '';

            this.categories.forEach(category => {
                const categoryElement = this.createCategoryElement(category);
                mainCategoriesContainer.appendChild(categoryElement);
            });
        }

        createCategoryElement(category) {
            const element = document.createElement('div');
            element.classList.add('category-item');
            element.dataset.id = category.id_kategori;
            element.textContent = category.nama_kategori;

            if (category.sub_kategori && category.sub_kategori.length > 0) {
                const chevronIcon = document.createElement('span');
                chevronIcon.classList.add('float-right');
                chevronIcon.innerHTML = '<i class="fa fa-chevron-right"></i>';
                element.appendChild(chevronIcon);
            }

            element.addEventListener('click', () => {
                if (!category.sub_kategori || category.sub_kategori.length === 0) {
                    this.selectCategory(category);
                }
            });

            element.addEventListener('mouseenter', () => {
                this.showSubcategories(category);
            });

            return element;
        }

        showSubcategories(category) {
            const subcategoriesPanel = document.getElementById('subcategories-panel');
            subcategoriesPanel.innerHTML = '';

            if (category.sub_kategori && category.sub_kategori.length > 0) {
                category.sub_kategori.forEach(subCat => {
                    const subCategoryItem = document.createElement('div');
                    subCategoryItem.classList.add('subcategory-item');
                    subCategoryItem.dataset.id = subCat.id_kategori;
                    subCategoryItem.textContent = subCat.nama_kategori;

                    subCategoryItem.addEventListener('click', () => {
                        this.selectCategory(subCat, `${category.nama_kategori} - ${subCat.nama_kategori}`);
                    });

                    subcategoriesPanel.appendChild(subCategoryItem);
                });

                subcategoriesPanel.style.display = 'block';
            } else {
                subcategoriesPanel.style.display = 'none';
            }
        }

        selectCategory(category, displayName = category.nama_kategori) {
            document.getElementById('cat_id').value = displayName;
            document.getElementById('selected_kategori_id').value = category.id_kategori;
            document.getElementById('category-dropdown').style.display = 'none';
        }

        setupCategoryDropdown() {
            const categoryInput = document.getElementById('cat_id');
            const categoryDropdown = document.getElementById('category-dropdown');

            categoryInput.addEventListener('click', () => {
                categoryDropdown.style.display = 'block';
            });

            document.addEventListener('click', (event) => {
                if (!categoryDropdown.contains(event.target) && 
                    event.target !== categoryInput) {
                    categoryDropdown.style.display = 'none';
                }
            });
        }

        // Form Population Methods
        populateForm(product) {
            // Populate basic product information
            document.getElementById('product_id').value = product.id_produk;
            document.getElementById('nama_produk').value = product.nama_produk;
            document.getElementById('deskripsi').value = product.deskripsi;
            document.getElementById('status').value = product.status;

            // Populate category
            const selectedCategory = this.findCategoryById(product.id_kategori);
            if (selectedCategory) {
                document.getElementById('cat_id').value = selectedCategory.nama_kategori;
                document.getElementById('selected_kategori_id').value = selectedCategory.id_kategori;
            }

            // Populate detail product information
            if (product.detail_produk) {
                const detailProduk = product.detail_produk;
                $('#detail_description').summernote('code', detailProduk.deskripsi_detail);
                document.getElementById('url_video').value = detailProduk.url_video;
            }

            // Render images and variations
            this.renderProductImages(product.gambar_produk);
            this.renderProductVariations(product.produk_variasi);
        }

        findCategoryById(id) {
            return this.categories.find(category => 
                category.id_kategori === id || 
                category.sub_kategori?.some(sub => sub.id_kategori === id)
            );
        }

        handleImageUpload(event) {
            const files = Array.from(event.target.files);
            const remainingSlots = this.maxImages - this.productImages.length;

            if (files.length + this.productImages.length > this.maxImages) {
                this.showErrorMessage(`Maksimal ${this.maxImages} gambar. Anda mencoba menambahkan ${files.length} gambar sementara hanya ${remainingSlots} slot tersisa.`);
                return;
            }

            files.forEach(file => {
                // Validate file type and size
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                const maxSize = 5120 * 1024; // 5MB in bytes

                if (!validTypes.includes(file.type)) {
                    this.showErrorMessage('Format gambar harus JPEG, PNG, atau JPG');
                    return;
                }
                if (file.size > maxSize) {
                    this.showErrorMessage('Ukuran gambar maksimal 5MB');
                    return;
                }

                this.productImages.push(file);
                const imageWrapper = this.createImageElement(file, this.productImages.length - 1);
                document.getElementById('existing-product-images').appendChild(imageWrapper);
            });

            this.updateImageCount();
        }

        removeImage(index) {
            this.productImages.splice(index, 1);
            this.renderProductImages(this.productImages);
        }

        updateImageCount() {
            const countElement = document.getElementById('image-count'); // Add this element to your HTML
            if (countElement) {
                countElement.textContent = `${this.productImages.length}/${this.maxImages}`;
            }
        }

        // Image and Variation Rendering
        renderProductImages(images) {
            const container = document.getElementById('existing-product-images');
            container.innerHTML = '';
            this.productImages = images ? [...images] : [];
            
            this.productImages.forEach((image, index) => {
                const imageWrapper = this.createImageElement(image, index);
                container.appendChild(imageWrapper);
            });
            this.updateImageCount();
        }

        // Modified createImageElement method
        createImageElement(image, index) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('existing-image-wrapper', 'position-relative', 'mr-2', 'mb-2');
        wrapper.dataset.index = index;
        if (image.id_gambar) {
            wrapper.dataset.imageId = image.id_gambar; // Store ID for existing images
        }

        const img = document.createElement('img');
        img.classList.add('img-thumbnail');
        img.style.maxWidth = '150px';
        img.style.maxHeight = '150px';
        
        if (image.gambar) {
            img.src = image.gambar; // Existing image URL
        } else if (image instanceof File) {
            img.src = URL.createObjectURL(image); // New image preview
        }

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.classList.add('btn', 'btn-danger', 'btn-sm', 'position-absolute');
        removeButton.style.top = '0';
        removeButton.style.right = '0';
        removeButton.textContent = 'X';
        removeButton.onclick = () => this.removeImage(index);

        wrapper.appendChild(img);
        wrapper.appendChild(removeButton);
        return wrapper;
    }

        renderProductVariations(variations) {
            const container = document.getElementById('variations-table-body');
            container.innerHTML = '';

            if (variations.length === 0) {
                container.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada variasi produk.</td></tr>';
                return;
            }

            variations.forEach(variation => {
                const row = this.createVariationRow(variation);
                container.appendChild(row);
            });

            document.addEventListener('input', (event) => {
                const target = event.target;
                
                // Format rupiah untuk input hpp dan harga
                if (target.name === 'hpp[]' || target.name === 'harga[]') {
                    // Simpan posisi kursor
                    const cursorPosition = target.selectionStart;
                    const originalLength = target.value.length;
                    
                    // Hapus semua karakter non-angka
                    let value = target.value.replace(/\D/g, '');
                    
                    // Format sebagai mata uang Rupiah
                    const formattedValue = new Intl.NumberFormat('id-ID').format(value);
                    target.value = formattedValue;
                    
                    // Menyesuaikan posisi kursor setelah format
                    const newPosition = cursorPosition + (target.value.length - originalLength);
                    target.setSelectionRange(newPosition, newPosition);
                    
                    // Validasi HPP < Harga
                    const row = target.closest('tr');
                    this.validatePricing(row);
                }
            });
        }

        createVariationRow(variation) {
            const row = document.createElement('tr');
            row.dataset.variationId = variation.id_produk_variasi;

            // Tipe & Opsi Variasi (Tidak bisa diedit)
            const typeAndOptionsCell = document.createElement('td');
            if (variation.detail_produk_variasi && variation.detail_produk_variasi.length > 0) {
                typeAndOptionsCell.textContent = variation.detail_produk_variasi
                    .map(detail => `${detail.opsi_variasi.tipe_variasi.nama_tipe}: ${detail.opsi_variasi.nama_opsi}`)
                    .join(', ');
            } else {
                typeAndOptionsCell.textContent = 'Default';
            }
            row.appendChild(typeAndOptionsCell);

            // Stok
            const stokCell = document.createElement('td');
            const stokInput = this.createInputField('stok[]', 'number', variation.stok);
            stokCell.appendChild(stokInput);
            row.appendChild(stokCell);

            // Berat
            const beratCell = document.createElement('td');
            const beratInput = this.createInputField('berat[]', 'number', variation.berat);
            beratCell.appendChild(beratInput);
            row.appendChild(beratCell);

            // HPP
            const hppCell = document.createElement('td');
            const hppInput = this.createInputField('hpp[]', 'text', variation.hpp);
            hppCell.appendChild(hppInput);
            row.appendChild(hppCell);

            // Harga
            const hargaCell = document.createElement('td');
            const hargaInput = this.createInputField('harga[]', 'text', variation.harga);
            hargaCell.appendChild(hargaInput);
            row.appendChild(hargaCell);

            // Status
            const statusCell = document.createElement('td');
            const statusSelect = this.createStatusSelect(variation.status);
            statusCell.appendChild(statusSelect);
            row.appendChild(statusCell);

           // Gambar
            const gambarCell = document.createElement('td');

            // Tampilkan gambar lama jika ada
            if (variation.gambar_variasi && variation.gambar_variasi.length > 0) {
                const img = document.createElement('img');
                img.src = variation.gambar_variasi[0].gambar;
                img.style.width = '50px';
                img.style.height = '50px';
                gambarCell.appendChild(img);
            }

            // Input untuk gambar baru
            const gambarInput = document.createElement('input');
            gambarInput.type = 'file';
            gambarInput.name = 'gambar_variasi';
            gambarInput.accept = 'image/jpeg,image/png,image/jpg';

            // Tambahkan event listener untuk menghapus gambar lama ketika ada gambar baru
            gambarInput.addEventListener('change', () => {
                const img = gambarCell.querySelector('img');
                if (img) {
                    gambarCell.removeChild(img); // Hapus gambar lama dari tampilan
                }
            });

            gambarCell.appendChild(gambarInput);
            row.appendChild(gambarCell);

            // Panggil validasi setelah semua elemen dibuat
            this.validatePricing(row);

            // Tambahkan event listener untuk validasi saat input berubah
            stokInput.addEventListener('input', () => this.validatePricing(row));
            beratInput.addEventListener('input', () => this.validatePricing(row));
            hppInput.addEventListener('input', () => this.validatePricing(row));
            hargaInput.addEventListener('input', () => this.validatePricing(row));

            return row;
        }

        validatePricing(row) {
            const stokInput = row.querySelector('input[name="stok[]"]');
            const beratInput = row.querySelector('input[name="berat[]"]');
            const hppInput = row.querySelector('input[name="hpp[]"]');
            const hargaInput = row.querySelector('input[name="harga[]"]');

            // Konversi nilai input ke angka
            const stok = parseInt(stokInput.value) || 0;
            const berat = parseFloat(beratInput.value) || 0;
            const hpp = parseInt(hppInput.value.replace(/\D/g, '')) || 0;
            const harga = parseInt(hargaInput.value.replace(/\D/g, '')) || 0;

            // Hapus feedback sebelumnya
            const existingFeedbacks = row.querySelectorAll('.invalid-feedback');
            existingFeedbacks.forEach(feedback => feedback.remove());

            // Reset class is-invalid
            stokInput.classList.remove('is-invalid');
            beratInput.classList.remove('is-invalid');
            hppInput.classList.remove('is-invalid');
            hargaInput.classList.remove('is-invalid');

            // Validasi Stok (maksimum 10.000)
            if (stok > 10000) {
                stokInput.classList.add('is-invalid');
                const stokError = document.createElement('div');
                stokError.classList.add('invalid-feedback');
                stokError.textContent = 'Stok tidak boleh lebih dari 10.000';
                stokInput.parentNode.appendChild(stokError);
            } else if (stok < 0) {
                stokInput.classList.add('is-invalid');
                const stokError = document.createElement('div');
                stokError.classList.add('invalid-feedback');
                stokError.textContent = 'Stok harus bilangan positif';
                stokInput.parentNode.appendChild(stokError);
            }

            // Validasi Berat (maksimum 100)
            if (berat > 100) {
                beratInput.classList.add('is-invalid');
                const beratError = document.createElement('div');
                beratError.classList.add('invalid-feedback');
                beratError.textContent = 'Berat tidak boleh lebih dari 100';
                beratInput.parentNode.appendChild(beratError);
            } else if (berat < 0) {
                beratInput.classList.add('is-invalid');
                const beratError = document.createElement('div');
                beratError.classList.add('invalid-feedback');
                beratError.textContent = 'Berat harus bilangan positif';
                beratInput.parentNode.appendChild(beratError);
            }

            // Validasi HPP < Harga
            if (hpp >= harga && harga > 0) {
                hppInput.classList.add('is-invalid');
                hargaInput.classList.add('is-invalid');
                const pricingError = document.createElement('div');
                pricingError.classList.add('invalid-feedback');
                pricingError.textContent = 'Harga harus lebih besar dari HPP';
                hargaInput.parentNode.appendChild(pricingError);
            }
        }

        // Fungsi untuk membuat input field
        createInputField(name, type, value) {
            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            input.classList.add('form-control');
            
            if (name === 'hpp[]' || name === 'harga[]') {
                // Format nilai sebagai mata uang Rupiah
                input.value = new Intl.NumberFormat('id-ID').format(value);
                
                // Tambahkan atribut untuk validasi
                input.autocomplete = 'off';
            } else {
                input.value = value;
            }

            if (type === 'number') {
                input.min = 0;
                input.required = true;
            }

            return input;
        }

        createStatusSelect(selectedStatus) {
            const select = document.createElement('select');
            select.name = 'status[]';
            select.classList.add('form-control');
            const options = ['aktif', 'nonaktif'];

            options.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option;
                opt.textContent = option.charAt(0).toUpperCase() + option.slice(1);
                if (option === selectedStatus) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });

            return select;
        }

        validateAllRows() {
            const rows = document.querySelectorAll('#variations-table-body tr');
            let isValid = true;

            rows.forEach(row => {
                this.validatePricing(row); // Jalankan validasi untuk setiap baris

                // Periksa apakah ada input yang memiliki class 'is-invalid'
                if (row.querySelector('.is-invalid')) {
                    isValid = false; // Jika ada error, set isValid ke false
                }
            });

            return isValid; // Kembalikan status validasi
        }

        // Form Submission
        handleFormSubmit(event) {
            event.preventDefault();
            this.updateProduct();
        }

        async updateProduct() {
            const submitButton = document.querySelector('button[type="submit"]');

            if (!this.validateAllRows()) {
                Swal.fire({
                    title: "Validasi Gagal",
                    text: "Terdapat kesalahan pada data variasi. Silakan periksa kembali.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return; // Hentikan proses jika validasi gagal
            }

            // Tampilkan loading state dan nonaktifkan tombol
            submitButton.disabled = true;
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

            const formData = new FormData(this.formElement);
            const productPayload = this.createProductPayload();

            console.log('Payload (Before FormData):', productPayload);

            formData.append('_method', 'PUT');

           // First loop (incorrectly includes all basic fields)
           for (const key in productPayload) {
            if (key === 'detail_produk') {
                for (const detailKey in productPayload.detail_produk) {
                    formData.append(`detail_produk[${detailKey}]`, productPayload.detail_produk[detailKey]);
                }
            } else if (key === 'gambar_produk' && productPayload.gambar_produk) {
                // Only send new File objects, not existing URLs
                productPayload.gambar_produk.forEach((img, index) => {
                    if (img instanceof File) {
                        formData.append(`gambar_produk[${index}]`, img);
                    }
                    // Note: We don't send existing URLs anymore
                });
            } else if (key !== 'variasi_existing' && key !== 'variasi_baru') {
                formData.append(key, productPayload[key]);
            }
        }

            // Handle existing variations
            for (const variation of productPayload.variasi_existing) {
                const variationId = variation.id_produk_variasi;
                
                // Append all fields except gambar
                for (const key in variation) {
                    if (key !== 'gambar') {
                        formData.append(`variasi_existing[${variationId}][${key}]`, variation[key]);
                    }
                }
                
                // Handle gambar field separately
                if (variation.gambar && variation.gambar.length > 0) {
                    formData.append(`variasi_existing[${variationId}][gambar][0]`, variation.gambar[0]);
                }
            }

            // Handle new variations
            productPayload.variasi_baru.forEach((variation, index) => {
                // Append tipe_variasi dan opsi_variasi
                variation.tipe_variasi.forEach((tipe, tipeIndex) => {
                    formData.append(`variasi_baru[${index}][tipe_variasi][${tipeIndex}][id_tipe_variasi]`, tipe.id_tipe_variasi);
                    tipe.opsi_variasi.forEach((opsi, opsiIndex) => {
                        if (opsi.id_opsi_variasi) {
                            formData.append(`variasi_baru[${index}][tipe_variasi][${tipeIndex}][opsi_variasi][${opsiIndex}][id_opsi_variasi]`, opsi.id_opsi_variasi);
                        } else {
                            formData.append(`variasi_baru[${index}][tipe_variasi][${tipeIndex}][opsi_variasi][${opsiIndex}][nama_opsi]`, opsi.nama_opsi);
                        }
                    });
                });

                // Append detail variasi baru
                formData.append(`variasi_baru[${index}][stok]`, variation.stok);
                formData.append(`variasi_baru[${index}][berat]`, variation.berat);
                formData.append(`variasi_baru[${index}][hpp]`, variation.hpp);
                formData.append(`variasi_baru[${index}][harga]`, variation.harga);
                formData.append(`variasi_baru[${index}][status]`, variation.status);

                // Append gambar variasi baru (jika ada)
                if (variation.gambar && variation.gambar.length > 0) {
                    formData.append(`variasi_baru[${index}][gambar][0]`, variation.gambar[0]);
                }
            });
            try {
                const response = await fetch(`${getApiBaseUrl()}/api/produk/edit/${this.productId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Authorization': `Bearer ${getJwtToken()}`
                    },
                    contentType: false,
                    processData: false
                });

                const data = await response.json();
                console.log('Response:', data);

                if (!response.ok) {
                    throw new ValidationError(data);
                }

                if (data.status === 'success') {
                    this.showSuccessMessage('Produk berhasil diperbarui');
                } else {
                    this.showErrorMessage(data.message || 'Gagal memperbarui produk');
                }
            } catch (error) {
                console.error("Error updating product:", error);

                if (error instanceof ValidationError) {
                    this.showErrorMessage(error.message);
                } else {
                    this.showErrorMessage('Terjadi kesalahan saat memperbarui produk.');
                }
            } finally {
                // Kembalikan tombol ke keadaan semula
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        }

        createProductPayload() {
            // Data variasi yang sudah ada
            const existingVariations = Array.from(document.querySelectorAll('#variations-table-body tr')).map(row => {
                const statusSelect = row.querySelector('select[name="status[]"]');
                const status = statusSelect ? statusSelect.value.toLowerCase().trim() : 'aktif';

                const gambarInput = row.querySelector('input[name="gambar_variasi"]');
                let gambar = []; // Default: array kosong

                // Jika ada gambar baru yang diunggah
                if (gambarInput && gambarInput.files.length > 0) {
                    gambar = [gambarInput.files[0]]; // Masukkan gambar baru ke dalam array
                } 
                console.log('gambar', gambar)

                const hpp = parseFloat(row.querySelector('input[name="hpp[]"]').value.replace(/[^0-9]/g, '')) || 0;
                const harga = parseFloat(row.querySelector('input[name="harga[]"]').value.replace(/[^0-9]/g, '')) || 0;

                return {
                    id_produk_variasi: row.dataset.variationId, // Pastikan data-variation-id ada di baris tabel
                    stok: parseInt(row.querySelector('input[name="stok[]"]').value) || 0,
                    berat: parseFloat(row.querySelector('input[name="berat[]"]').value) || 0,
                    hpp: hpp,
                    harga: harga,
                    status: ['aktif', 'nonaktif'].includes(status) ? status : 'aktif',
                    gambar: gambar
                };
            });

            // Data variasi baru
            const newVariations = this.newVariationsFromModal || [];

            const payload = {
                id_kategori: document.getElementById('selected_kategori_id').value,
                nama_produk: document.getElementById('nama_produk').value,
                deskripsi: document.getElementById('deskripsi').value,
                status: document.getElementById('status').value,
                detail_produk: {
                    deskripsi_detail: document.getElementById('detail_produk_deskripsi_detail').value,
                    url_video: document.getElementById('url_video').value
                },
                variasi_existing: existingVariations,
                variasi_baru: newVariations
            };

            // Only include gambar_produk if new images were added
            const existingImageIds = this.productImages
                .filter(img => img.id_gambar)
                .map(img => img.id_gambar); // This already creates an array
            
            const newImages = this.productImages.filter(img => img instanceof File);

            if (existingImageIds.length > 0) {
                payload.existing_image_ids = existingImageIds; // Always an array
            }
            if (newImages.length > 0) {
                payload.gambar_produk = newImages;
            }

            return payload;
        }
        async addNewVariation() {
            console.log("Memanggil");
            try {
                const productVariationsResponse = await this.fetchData(`${getApiBaseUrl()}/api/variasi-produk`);
                if (productVariationsResponse?.status === 'success') {
                    this.productVariations = productVariationsResponse.data; // Simpan ke properti class
                    const productVariationTypes = productData.produk_variasi.reduce((types, variation) => {
                        variation.detail_produk_variasi.forEach(detail => {
                            if (!types.includes(detail.opsi_variasi.tipe_variasi.id_tipe_variasi)) {
                                types.push(detail.opsi_variasi.tipe_variasi.id_tipe_variasi);
                            }
                        });
                        return types;
                    }, []);
                    const filteredVariations = this.productVariations.filter(type => 
                        productVariationTypes.includes(type.id_tipe_variasi)
                    );

                    if (filteredVariations) {
                        console.log(filteredVariations);
                        this.showAddVariationModal(filteredVariations, productData);
                    } else {
                        throw new Error("Gagal memuat data variasi.");
                    }
                } else {
                    throw new Error("Gagal mengambil data tipe variasi.");
                }
            } catch (error) {
                console.error("Error di addNewVariation():", error);
                this.showErrorMessage(error.message);
            }
        }
        
        showAddVariationModal(productVariations, productData) {
            this.modalBody.innerHTML = '';
            const modalTitle = document.createElement('h5');
            modalTitle.className = 'modal-title';
            modalTitle.textContent = 'Tambah Variasi Baru';
            this.modalBody.appendChild(modalTitle);

            const form = document.createElement('form');

            // Fungsi untuk memeriksa validasi
            const validateForm = () => {
                let isValid = true;
                productVariations.forEach(type => {
                    const checkboxes = form.querySelectorAll(`input[name="variasi_baru[${type.id_tipe_variasi}][]"]:checked`);
                    if (checkboxes.length === 0) {
                        isValid = false;
                    }
                });
                return isValid;
            };

            // Fungsi untuk mengupdate status tombol "Input Detail"
            const updateInputDetailButton = () => {
                if (validateForm()) {
                    inputDetailButton.disabled = false;
                } else {
                    inputDetailButton.disabled = true;
                }
            };

            productVariations.forEach(type => {
                const typeDiv = document.createElement('div');
                typeDiv.className = 'mb-4';
                const label = document.createElement('label');
                label.textContent = type.nama_tipe;
                label.className = 'form-label fw-bold';
                typeDiv.appendChild(label);

                // Gabungkan opsi yang sudah ada dengan opsi sementara (temporaryOptions)
                const allOptions = [
                    ...type.opsi_variasi,
                    ...temporaryOptions.filter(opt => opt.id_tipe_variasi === type.id_tipe_variasi)
                ];

                allOptions.forEach(option => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'form-check';
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input';
                    checkbox.name = `variasi_baru[${type.id_tipe_variasi}][]`;
                    checkbox.value = option.id_opsi_variasi;
                    checkbox.id = `option_${option.id_opsi_variasi}`;

                    // Cek apakah opsi sudah digunakan di produk yang ada
                    const isOptionUsed = productData.produk_variasi.some(variation =>
                        variation.detail_produk_variasi.some(detail =>
                            detail.id_opsi_variasi === option.id_opsi_variasi
                        )
                    );
                    if (isOptionUsed) {
                        checkbox.disabled = true;
                    }

                    // Cek apakah opsi ini sudah dipilih (ada di selectedVariations)
                    const isOptionSelected = selectedVariations.some(v => 
                        v.id_tipe_variasi === type.id_tipe_variasi && v.id_opsi_variasi === option.id_opsi_variasi
                    );
                    if (isOptionSelected) {
                        checkbox.checked = true;
                    }

                    // Tambahkan event listener untuk menyimpan perubahan ke selectedVariations
                    checkbox.addEventListener('change', () => {
                        if (checkbox.checked) {
                            this.addSelectedVariation(type, option);
                        } else {
                            this.removeSelectedVariation(type, option);
                        }
                        // Update status tombol "Input Detail" setiap kali ada perubahan pada checkbox
                        updateInputDetailButton();
                    });

                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label';
                    optionLabel.htmlFor = `option_${option.id_opsi_variasi}`;
                    optionLabel.textContent = option.nama_opsi;

                    if (String(option.id_opsi_variasi).startsWith('temp_')) {
                        optionLabel.classList.add('text-primary');
                    }

                    optionDiv.appendChild(checkbox);
                    optionDiv.appendChild(optionLabel);
                    typeDiv.appendChild(optionDiv);
                });

                // Tombol untuk menambahkan opsi baru
                const addOptionButton = document.createElement('button');
                addOptionButton.type = 'button';
                addOptionButton.className = 'btn btn-sm btn-outline-primary mt-2';
                addOptionButton.textContent = 'Tambah Opsi Baru';
                addOptionButton.addEventListener('click', () => {
                    this.showAddNewOptionForm(type, productVariations, productData);
                });
                typeDiv.appendChild(addOptionButton);
                form.appendChild(typeDiv);
            });

            // Tombol "Input Detail"
            const inputDetailButton = document.createElement('button');
            inputDetailButton.type = 'button';
            inputDetailButton.className = 'btn btn-primary mt-3';
            inputDetailButton.textContent = 'Input Detail';
            inputDetailButton.disabled = true; // Nonaktifkan tombol secara default
            inputDetailButton.addEventListener('click', () => {
                if (validateForm()) {
                    this.processNewVariations(productVariations, productData);
                } else {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Harap pilih setidaknya satu opsi dari setiap tipe variasi.",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                }
            });
            form.appendChild(inputDetailButton);

            this.modalBody.appendChild(form);
            $(this.modal).modal('show');

            // Jalankan validasi awal untuk mengupdate status tombol "Input Detail"
            updateInputDetailButton();
        }

        isOptionSelected(typeId, optionId) {
            return temporaryOptions.some(opt => opt.id_tipe_variasi === typeId && opt.id_opsi_variasi === optionId) ||
                this.newVariations.some(variation => variation.opsi_variasi.some(opt => opt.id_opsi_variasi === optionId) || variation.new_options.some(opt => opt.id_opsi_variasi === optionId));
        }


        addSelectedVariation(type, option) {
            const existingVariation = selectedVariations.find(v => v.id_tipe_variasi === type.id_tipe_variasi && v.id_opsi_variasi === option.id_opsi_variasi);
            if (!existingVariation) {
                selectedVariations.push({
                    id_tipe_variasi: type.id_tipe_variasi,
                    nama_tipe: type.nama_tipe,
                    id_opsi_variasi: option.id_opsi_variasi,
                    nama_opsi: option.nama_opsi
                });
            }
            console.log("selectedVariations:", selectedVariations);
        }


        removeSelectedVariation(type, option) {
            selectedVariations = selectedVariations.filter(v => !(v.id_tipe_variasi === type.id_tipe_variasi && v.id_opsi_variasi === option.id_opsi_variasi));
            console.log("selectedVariations:", selectedVariations);
        }


        showAddNewOptionForm(type, productVariations, productData) {
            this.modalBody.innerHTML = '';
            const modalTitle = document.createElement('h5');
            modalTitle.className = 'modal-title';
            modalTitle.textContent = `Tambah Opsi Baru untuk ${type.nama_tipe}`;
            this.modalBody.appendChild(modalTitle);

            const form = document.createElement('form');
            const inputGroup = document.createElement('div');
            inputGroup.className = 'mb-3';
            const inputLabel = document.createElement('label');
            inputLabel.textContent = 'Nama Opsi Baru';
            inputLabel.className = 'form-label';
            inputGroup.appendChild(inputLabel);

            const inputField = document.createElement('input');
            inputField.type = 'text';
            inputField.className = 'form-control';
            inputField.placeholder = 'Masukkan nama opsi baru';
            inputField.required = true;
            inputGroup.appendChild(inputField);
            form.appendChild(inputGroup);

            const saveButton = document.createElement('button');
            saveButton.type = 'button';
            saveButton.className = 'btn btn-primary';
            saveButton.textContent = 'Simpan';
            saveButton.addEventListener('click', () => {
                const newOptionName = inputField.value.trim();
                if (newOptionName) {
                    const isOptionExist = type.opsi_variasi.some(opt => opt.nama_opsi === newOptionName) || temporaryOptions.some(opt => opt.nama_opsi === newOptionName && opt.id_tipe_variasi === type.id_tipe_variasi);
                    if (isOptionExist) {
                        Swal.fire({
                            title: "Peringatan!",
                            text: "Opsi dengan nama yang sama sudah ada.",
                            icon: "warning",
                            confirmButtonText: "OK"
                        });
                    } else {
                        this.saveTemporaryOption(type, newOptionName, productVariations, productData);
                    }
                } else {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Nama opsi tidak boleh kosong.",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                }
            });
            form.appendChild(saveButton);

            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.className = 'btn btn-secondary ms-2';
            cancelButton.textContent = 'Batal';
            cancelButton.addEventListener('click', () => {
                this.showAddVariationModal(productVariations, productData);
            });
            form.appendChild(cancelButton);

            this.modalBody.appendChild(form);
        }

        saveTemporaryOption(type, newOptionName, productVariations, productData) {
            const newOption = {
                id_tipe_variasi: type.id_tipe_variasi,
                nama_tipe: type.nama_tipe,
                nama_opsi: newOptionName,
                id_opsi_variasi: `temp_${Date.now()}`
            };
            temporaryOptions.push(newOption);
            this.addSelectedVariation(type, newOption); // Tambahkan ke selectedVariations
            this.showAddVariationModal(productVariations, productData);
        }

        // Fungsi untuk menghapus opsi baru sementara
        removeTemporaryOption(optionId) {
            temporaryOptions = temporaryOptions.filter(opt => opt.id_opsi_variasi !== optionId);
        }

        validateSelectedVariations(selectedVariations, productVariations) {
            const selectedTypes = new Set(selectedVariations.map(opt => opt.id_tipe_variasi));
            const allTypes = new Set(productVariations.map(type => type.id_tipe_variasi));

            // Pastikan semua tipe variasi dipilih
            if (selectedTypes.size !== allTypes.size) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Anda harus memilih setidaknya satu opsi dari setiap tipe variasi.",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
                return false;
            }

            return true;
        }

        processNewVariations(productVariations) {
            const combinations = [];

            // Kelompokkan selectedVariations berdasarkan id_tipe_variasi
            const groupedOptions = {};
            selectedVariations.forEach(option => {
                if (!groupedOptions[option.id_tipe_variasi]) {
                    groupedOptions[option.id_tipe_variasi] = [];
                }
                groupedOptions[option.id_tipe_variasi].push(option);
            });

            // Konversi groupedOptions ke array untuk memudahkan pemrosesan
            const types = productVariations.map(type => ({
                id_tipe_variasi: type.id_tipe_variasi,
                nama_tipe: type.nama_tipe,
                opsi_variasi: groupedOptions[type.id_tipe_variasi] || []
            }));

            // Fungsi rekursif untuk menghasilkan kombinasi
            const generateCombinations = (currentCombination, index) => {
                if (index === types.length) {
                    combinations.push(currentCombination);
                    return;
                }

                types[index].opsi_variasi.forEach(option => {
                    generateCombinations([...currentCombination, option], index + 1);
                });
            };

            // Mulai menghasilkan kombinasi
            generateCombinations([], 0);
            
            console.log("Kombinasi Variasi:", combinations);
            this.newVariations = combinations.map(combination => ({
                opsi_variasi: combination
            }));

            $(this.modal).modal('hide');
            this.showInputDetailModal(this.newVariations);
        }

        showInputDetailModal(combinations) {
            // Buat elemen modal baru dengan lebar 2 kali lipat
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            modal.setAttribute('role', 'dialog');
            modal.innerHTML = `
                <div class="modal-dialog modal-xl" style="max-width: 90% !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Masukan Detail Variasi</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${this.generateVariationTable(combinations)}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" id="saveVariationDetails">Simpan</button>
                        </div>
                    </div>
                </div>
            `;
            $(document).on('input', '.currency', function() {
                let value = $(this).val().replace(/\D/g, ''); // Hanya angka
                let formattedValue = new Intl.NumberFormat('id-ID').format(value); // Format Rupiah

                $(this).val(formattedValue); // Tampilkan format Rupiah
                $(this).siblings('input[type="hidden"]').val(value); // Simpan tanpa format
            });

            // Validasi HPP harus lebih kecil dari Harga
            $(document).on('input', '.hpp, .price', function() {
                let $row = $(this).closest('.variation-detail-row');
                let hpp = parseInt($row.find('.hpp-hidden').val() || 0, 10);
                let price = parseInt($row.find('.price-hidden').val() || 0, 10);

                let $hppInput = $row.find('.hpp');
                let $priceInput = $row.find('.price');

                // Reset error
                $hppInput.removeClass('is-invalid');
                $priceInput.removeClass('is-invalid');
                $row.find('.invalid-feedback').remove();

                if (hpp >= price) {
                    $hppInput.addClass('is-invalid');
                    $priceInput.addClass('is-invalid');
                    $priceInput.after('<div class="invalid-feedback">Harga harus lebih besar dari HPP</div>');
                }
            });

            // Tambahkan modal ke body
            document.body.appendChild(modal);

            // Tampilkan modal
            $(modal).modal('show');

            // Tambahkan event listener untuk tombol simpan
            document.querySelector('#saveVariationDetails').addEventListener('click', () => {
                this.processVariationDetails(combinations, modal); // Panggil fungsi baru
                const addVariationButton = document.querySelector('#addVariationButton');
                if (addVariationButton) {
                    addVariationButton.textContent = 'Edit Variasi Baru';
                    addVariationButton.classList.remove('btn-primary');
                    addVariationButton.classList.add('btn-warning');
                }
            });

            // Tambahkan event listener untuk tombol remove
            modal.querySelectorAll('.remove-variation').forEach((button, index) => {
                button.addEventListener('click', () => {
                    this.removeVariation(index, combinations, modal);
                });
            });
        }

        generateVariationTable(combinations) {
            return `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 160px";>Variasi</th>
                            <th>Stok</th>
                            <th>Berat</th>
                            <th>HPP</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Gambar</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="new-variations-table-body">
                        ${combinations.map((combination, index) => `
                            <tr class="variation-detail-row" data-index="${index}">
                                <td>${combination.opsi_variasi.map(opt => opt.nama_opsi).join(' - ')}</td>
                                <td>
                                    <input type="number" class="form-control stock" 
                                        name="variations[${index}][stock]" 
                                        min="0" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control weight" 
                                        name="variations[${index}][weight]" 
                                        min="0" step="0.01" required>
                                </td>
                                 <td>
                                <input type="text" class="form-control hpp currency" 
                                    name="variations[${index}][hpp]" 
                                    required>
                                <input type="hidden" class="hpp-hidden" name="variations[${index}][hpp_value]">
                            </td>
                            <td>
                                <input type="text" class="form-control price currency" 
                                    name="variations[${index}][price]" 
                                    required>
                                <input type="hidden" class="price-hidden" name="variations[${index}][price_value]">
                            </td>
                                <td>
                                    <select class="form-control status" 
                                            name="variations[${index}][status]">
                                        <option value="aktif">Active</option>
                                        <option value="nonaktif">Inactive</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="file" class="form-control variation-image" style="width: 160px !important;"
                                        name="variations[${index}][image]" 
                                        accept="image/*">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-variation">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
        }

        removeVariation(index, combinations, modal) {
            // Hapus kombinasi dari array
            combinations.splice(index, 1);

            // Perbarui tabel di modal
            const modalBody = modal.querySelector('.modal-body');
            modalBody.innerHTML = this.generateVariationTable(combinations);

            // Tambahkan kembali event listener untuk tombol remove
            modalBody.querySelectorAll('.remove-variation').forEach((button, i) => {
                button.addEventListener('click', () => {
                    this.removeVariation(i, combinations, modal);
                });
            });

            // Jika tidak ada kombinasi tersisa, tutup modal
            if (combinations.length === 0) {
                $(modal).modal('hide');
                modal.remove();
            }
        }

        processVariationDetails(combinations, modal) {
            const newVariations = [];
            const rows = modal.querySelectorAll('#new-variations-table-body tr');

            rows.forEach((row, index) => {
                const stock = parseInt(row.querySelector(`input[name="variations[${index}][stock]"]`).value) || 0;
                const weight = parseFloat(row.querySelector(`input[name="variations[${index}][weight]"]`).value) || 0;
                const hpp = parseFloat(row.querySelector(`input[name="variations[${index}][hpp]"]`).value.replace(/[^0-9]/g, '')) || 0;
                const price = parseFloat(row.querySelector(`input[name="variations[${index}][price]"]`).value.replace(/[^0-9]/g, '')) || 0;
                const status = row.querySelector(`select[name="variations[${index}][status]"]`).value || 'aktif';
                const gambar = row.querySelector(`input[name="variations[${index}][image]"]`).files[0] || null;

                if (!stock || !weight || !hpp || !price) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Semua kolom harus diisi kecuali gambar",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                    isValid = false;
                    return;
                }

                if (stock > 10000) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Stok tidak boleh lebih dari 10.000",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                    isValid = false;
                    return;
                } else if (stock < 0) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Stok harus bilangan positif",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                    isValid = false;
                    return;
                }

                // Validasi berat (maksimum 100)
                if (weight > 100) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Berat tidak boleh lebih dari 100",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                    isValid = false;
                    return;
                } else if (weight < 0) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "Berat harus bilangan positif",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                    isValid = false;
                    return;
                }

                if (hpp >= price) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: "HPP tidak boleh lebih besar dari Harga",
                        icon: "warning",
                        confirmButtonText: "OK"
                    });
                    isValid = false;
                    return;
                }
                const combination = combinations[index];

                if (!combination) {
                    console.error("Kombinasi variasi tidak ditemukan untuk index:", index);
                    return;
                }

                const tipeVariasiArray = combination.opsi_variasi.map(opsi => {
                    const opsiVariasiArray = [];

                    if (String(opsi.id_opsi_variasi && opsi.id_opsi_variasi).startsWith('temp_')) {
                        opsiVariasiArray.push({ nama_opsi: opsi.nama_opsi }); // Simpan nama jika ID diawali 'temp_'
                    } else if (opsi.id_opsi_variasi) {
                        opsiVariasiArray.push({ id_opsi_variasi: opsi.id_opsi_variasi }); // Simpan ID jika tidak diawali 'temp_'
                    } else {
                        opsiVariasiArray.push({ nama_opsi: opsi.nama_opsi }); // Fallback: Simpan nama jika ID tidak ada
                    }   

                    return {
                        id_tipe_variasi: opsi.id_tipe_variasi, // id tipe variasi tetap disimpan
                        opsi_variasi: opsiVariasiArray
                    };
                });

                newVariations.push({ stok: stock, berat: weight, hpp: hpp, harga: price, status: status, gambar: gambar ? [gambar] : [] });

                console.log('newVariation', newVariations)
            });

            $(modal).modal('hide');
            modal.remove();

            this.newVariationsFromModal = newVariations;
        }

        showSuccessMessage(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 2000,
                showConfirmButton: false
            })
            // .then(() => {
            //     window.location.reload();
            // });
        }

        showErrorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: message
            });
        }
    }

    class ValidationError extends Error {
        constructor(errorData) {
            super('Validation Error');
            this.name = 'ValidationError';
            this.errors = errorData.errors || {};
        }
    }

    // Initialize the Product Edit Manager
    new ProductEditManager();
});
</script>
@endpush