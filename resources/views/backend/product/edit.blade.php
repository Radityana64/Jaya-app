@extends('backend.layouts.master')

@section('main-content')
<div class="container mt-5">
    <h2>Edit Produk</h2>
    <form id="editProductForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="product_id" name="product_id">

        <div class="form-group position-relative">
            <label for="cat_id">Kategori <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" id="cat_id" name="kategori" 
                       class="form-control category-autocomplete" 
                       placeholder="Pilih kategori" 
                       autocomplete="off" required>
                <input type="hidden" id="selected_kategori_id" name="id_kategori">
            </div>
            
            <div class="categories-container bg-white border shadow-sm mt-2" 
                 id="category-dropdown" style="display:none;">
                <div class="main-categories">
                    <!-- Kategori utama akan diisi secara dinamis -->
                </div>
                <div class="subcategories-panel" id="subcategories-panel">
                    <!-- Subkategori akan diisi secara dinamis -->
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
            <input id="gambar_produk" type="file" name="gambar_produk[]" multiple class="form-control">
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
            <div id="existing-variations-container"></div>
            <button type="button" id="add-new-variation" class="btn btn-primary mt-2">
                Tambah Variasi Baru
            </button>
        </div>

        <button type="submit" class="btn btn-success mt-3">Simpan Perubahan</button>
    </form>
</div>

@endsection

@push('styles')
<!-- Tambahkan style yang diperlukan -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<style>
    .categories-container {
            display: flex;
            position: absolute;
            background: white;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            width: calc(100% - 30px);
        }

        .main-categories {
            width: 250px;
            border-right: 1px solid #e0e0e0;
            padding: 10px;
            max-height: 400px;
            overflow-y: auto;
        }

        .subcategories-panel {
            width: 300px;
            padding: 10px;
            max-height: 400px;
            overflow-y: auto;
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

        .category-search {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    document.addEventListener('DOMContentLoaded', function() {
    class ProductEditManager {
        constructor() {
            this.productId = this.getProductIdFromUrl();
            this.categories = [];
            this.variations = [];
            this.formElement = document.getElementById('editProductForm');
            
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
            
            document.getElementById('gambar_produk').addEventListener('change', 
                this.handleImagePreview.bind(this)
            );
            
            document.getElementById('add-new-variation').addEventListener('click', 
                this.addNewVariation.bind(this)
            );
        }

        // Data Loading Methods
        async loadProductData() {
            try {
                const [categoriesResponse, productResponse] = await Promise.all([
                    this.fetchData('/api/kategori'),
                    this.fetchData(`/api/produk/${this.productId}`)
                ]);

                if (categoriesResponse?.status && productResponse?.status === 'success') {
                    this.categories = categoriesResponse.data;
                    this.populateForm(productResponse.data);
                    this.renderCategories();
                } else {
                    throw new Error('Gagal memuat data produk atau kategori');
                }
            } catch (error) {
                this.showErrorMessage(error.message);
            }
        }

        // Category Dropdown Methods
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

            element.addEventListener('click', () => this.selectCategory(category));
            
            return element;
        }

        selectCategory(category) {
            document.getElementById('cat_id').value = category.nama_kategori;
            document.getElementById('selected_kategori_id').value = category.id_kategori;
            document.getElementById('category-dropdown').style.display = 'none';
        }

        setupCategoryDropdown() {
            const categoryInput = document.getElementById('cat_id');
            const categoryDropdown = document.getElementById('category-dropdown');

            categoryInput.addEventListener('click', () => {
                categoryDropdown.style.display = 'block';
            });

            // Close dropdown when clicking outside
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

        // Image and Variation Rendering
        renderProductImages(images) {
            const container = document.getElementById('existing-product-images');
            container.innerHTML = '';

            images.forEach(image => {
                const imageWrapper = this.createImageElement(image);
                container.appendChild(imageWrapper);
            });
        }

        createImageElement(image) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('existing-image-wrapper', 'position-relative', 'mr-2', 'mb-2');

            const img = document.createElement('img');
            img.src = image.gambar;
            img.classList.add('img-thumbnail');
            img.style.maxWidth = '150px';
            img.style.maxHeight = '150px';

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.classList.add('btn', 'btn-danger', 'btn-sm', 'position-absolute');
            removeButton.style.top = '0';
            removeButton.style.right = '0';
            removeButton.textContent = 'X';
            removeButton.onclick = () => wrapper.remove();

            wrapper.appendChild(img);
            wrapper.appendChild(removeButton);

            return wrapper;
        }

        renderProductVariations(variations) {
            const container = document.getElementById('existing-variations-container');
            container.innerHTML = '';

            variations.forEach(variation => {
                const variationElement = this.createVariationElement(variation);
                container.appendChild(variationElement);
            });
        }

        createVariationElement(variation) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('existing-variation-item');
            wrapper.dataset.variationId = variation.id_produk_variasi;

            const titleElement = document.createElement('h5');
            titleElement.textContent = `Variasi: ${variation.detail_produk_variasi
                .map(detail => detail.opsi_variasi.nama_opsi)
                .join(', ')
            }`;

            const fields = [
                { name: 'stok[]', label: 'Stok', type: 'number', value: variation.stok },
                { name: 'berat[]', label: 'Berat', type: 'number', value: variation.berat },
                { name: 'hpp[]', label: 'HPP', type: 'number', value: variation.hpp },
                { name: 'harga[]', label: 'Harga', type: 'number', value: variation.harga }
            ];

            fields.forEach(field => {
                const inputField = this.createInputField(field.name, field.label, field.type, field.value);
                wrapper.appendChild(inputField);
            });

            const statusSelect = this.createStatusSelect(variation.status);
            wrapper.appendChild(statusSelect);

            return wrapper;
        }

        createInputField(name, label, type, value) {
            const wrapper = document.createElement('div');
            const labelElement = document.createElement('label');
            labelElement.textContent = label;

            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            input.value = value;
            input.min = 0;
            input.required = true;

            wrapper.appendChild(labelElement);
            wrapper.appendChild(input);
            return wrapper;
        }

        createStatusSelect(selectedStatus) {
            const wrapper = document.createElement('div');
            const labelElement = document.createElement('label');
            labelElement.textContent = 'Status';

            const select = document.createElement('select');
            select.name = 'status[]';
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

            wrapper.appendChild(labelElement);
            wrapper.appendChild(select);
            return wrapper;
        }

        // Form Submission
        handleFormSubmit(event) {
            event.preventDefault();
            this.updateProduct();
        }

        async updateProduct() {
            const formData = new FormData(this.formElement);
            const productPayload = this.createProductPayload();

            // Tambahkan payload sebagai JSON string
            formData.append('payload', JSON.stringify(productPayload));
            formData.append('_method', 'PUT');

            try {
                const response = await fetch(`/api/produk/edit/${this.productId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Authorization': `Bearer ${getJwtToken()}`
                    },
                    contentType: false,
                    processData: false,
                });

                const data = await response.json();

                if (!response.ok) {
                    // Tangani error dari server
                    throw new ValidationError(data);
                }

                if (data.status === 'success') {
                    this.showSuccessMessage('Produk berhasil diperbarui');
                } else {
                    this.showErrorMessage(data.message || 'Gagal memperbarui produk');
                }
            } catch (error) {
                if (error instanceof ValidationError) {
                    this.handleValidationErrors(error.errors || {});
                } else {
                    console.error('Unexpected error:', error);
                    this.showErrorMessage('Terjadi kesalahan saat menyimpan data');
                }
            }
        }

        createProductPayload() {
        const variations = Array.from(document.querySelectorAll('.existing-variation-item')).map(item => {
            const statusSelect = item.querySelector('select[name="status[]"]');
            const status = statusSelect ? statusSelect.value.toLowerCase().trim() : 'aktif';

            return {
                    id_produk_variasi: item.dataset.variationId,
                    stok: parseInt(item.querySelector('input[name="stok[]"]').value) || 0,
                    berat: parseFloat(item.querySelector('input[name="berat[]"]').value) || 0,
                    hpp: parseFloat(item.querySelector('input[name="hpp[]"]').value) || 0,
                    harga: parseFloat(item.querySelector('input[name="harga[]"]').value) || 0,
                    status: ['aktif', 'nonaktif'].includes(status) ? status : 'aktif'
                };
            });

            return {
                id_kategori: document.getElementById('selected_kategori_id').value,
                nama_produk: document.getElementById('nama_produk').value,
                deskripsi: document.getElementById('deskripsi').value,
                status: document.getElementById('status').value,
                detail_produk: {
                    deskripsi_detail: document.getElementById('detail_produk_deskripsi_detail').value,
                    url_video: document.getElementById('url_video').value
                },
                variasi_existing: variations,
                variasi_baru: [] // Tambahkan logika untuk variasi baru jika diperlukan
            };
        }

        // Error Handling
        handleValidationErrors(errors) {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            const errorMapping = {
                'nama_produk': '#nama_produk',
                'deskripsi': '#deskripsi',
                'status': '#status',
                'id_kategori': '#cat_id',
                'detail_produk.deskripsi_detail': '#detail_description',
                'detail_produk.url_video': '# url_video',
                'variasi_existing.*.stok': 'input[name="stok[]"]',
                'variasi_existing.*.berat': 'input[name="berat[]"]',
                'variasi_existing.*.hpp': 'input[name="hpp[]"]',
                'variasi_existing.*.harga': 'input[name="harga[]"]',
                'variasi_existing.*.status': 'select[name="status[]"]'
            };

            let errorMessage = 'Terjadi kesalahan validasi:\n';

            Object.keys(errors).forEach(key => {
                const errorTexts = errors[key];
                errorMessage += `- ${errorTexts.join(', ')}\n`;

                const matchingSelectors = Object.keys(errorMapping).filter(pattern => 
                    key.startsWith(pattern.replace(/\.\*/, ''))
                );

                if (matchingSelectors.length > 0) {
                    const selector = errorMapping[matchingSelectors[0]];
                    const elements = document.querySelectorAll(selector);
                    
                    elements.forEach(el => {
                        el.classList.add('is-invalid');
                        
                        const errorTooltip = document.createElement('div');
                        errorTooltip.classList.add('invalid-feedback');
                        errorTooltip.textContent = errorTexts.join(', ');
                        
                        const wrapper = el.closest('.form-group') || el.parentElement;
                        wrapper.appendChild(errorTooltip);
                    });
                }
            });

            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: errorMessage.replace(/\n/g, '<br>'),
                width: '600px'
            });
        }

        showSuccessMessage(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        }

        showErrorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: message
            });
        }

        handleImagePreview(event) {
            const files = event.target.files;
            const previewContainer = document.getElementById('existing-product-images');
            previewContainer.innerHTML = '';

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imgWrapper = document.createElement('div');
                    imgWrapper.classList.add('existing-image-wrapper', 'position-relative', 'mr-2', 'mb-2');
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-thumbnail');
                    img.style.maxWidth = '150px';
                    img.style.maxHeight = '150px';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-danger', 'btn-sm', 'position-absolute');
                    removeBtn.style.top = '0';
                    removeBtn.style.right = '0';
                    removeBtn.textContent = 'X';
                    removeBtn.addEventListener('click', () => {
                        imgWrapper.remove();
                    });

                    imgWrapper.appendChild(img);
                    imgWrapper.appendChild(removeBtn);
                    previewContainer.appendChild(imgWrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        addNewVariation() {
            // Logic to add a new variation
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