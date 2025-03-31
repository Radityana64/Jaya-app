@extends('backend.layouts.master')

@section('main-content')
<div class="container mt-5">
    <h2>Add Product</h2>
    <form id="productForm" enctype="multipart/form-data">
        @csrf
        <div class="form-group position-relative">
          <label for="cat_id">Kategori <span class="text-danger">*</span></label>
          <div class="input-group">
              <input type="text" id="cat_id" name="kategori" 
                    class="form-control category-autocomplete" 
                    placeholder="Search or select category" 
                    autocomplete="off" required>
              <input type="hidden" id="selected_kategori_id" name="id_kategori">
          </div>
          
          <div class="categories-container bg-white border shadow-sm mt-2" 
              id="category-dropdown" style="display:none;">
              <div class="main-categories">
                  <!-- Main categories will be dynamically populated here -->
              </div>
              <div class="subcategories-panel" id="subcategories-panel">
                  <!-- Subcategories will be dynamically populated here -->
              </div>
          </div>
      </div>

        <div class="form-group">
            <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
            <input id="nama_produk" type="text" name="nama_produk" placeholder="Masukan Nama Produk" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Singkat<span class="text-danger">*</span></label>
            <input id="deskripsi" type="text" name="deskripsi" placeholder="Masukan Deskripsi Singkat" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Detail Diskripsi</label>
            <div id="description"></div>
            <input type="hidden" name="detail_produk[deskripsi_detail]" id="detail_produk_deskripsi_detail">
        </div>

        <div class="form-group">
            <label for="detail_produk.url_video">URL Youtube Video </label>
            <input id="url_video" type="url" name="detail_produk[url_video]" placeholder="https://youtu.be/" class="form-control">
        </div>

        <div class="form-group">
            <label for="gambar_produk">Gambar Produk <span class="text-danger">*</span></label>
            <input type="file" name="gambar_produk[]" id="gambar_produk" class="form-control" accept="image/*" multiple>
            <div id="image-preview" class="mt-2"></div> <!-- Container untuk pratinjau -->
        </div>

        <div class="form-group">
            <label for="has_variation">Tambahkan Variasi</label>
            <input type="checkbox" id="has_variation" name="has_variation">
        </div>

        <div id="default-attributes" class="mb-3"> 
            <h5>Atribut Produk</h5>
            <div class="form-group">
                <label for="stok">Stok</label>
                <input type="number" name="stok" id="stok" class="form-control stock" min="0" max="10000" required>
            </div>
            <div class="form-group">
                <label for="berat">Berat (Kg)</label>
                <input type="number" name="berat" id="berat" class="form-control weight" min="0" max="100" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="hpp">HPP</label>
                <input type="text" name="hpp" id="hpp" class="form-control hpp currency" maxlength="11" required>
                <input type="hidden" class="hpp-hidden" name="hpp_value">
            </div>
            <div class="form-group">
                <label for="harga">Harga</label>
                <input type="text" name="harga" id="harga" class="form-control price currency" maxlength="11" required>
                <input type="hidden" class="price-hidden" name="price_value">
            </div>
            <div class="form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control status" required>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>

        <div id="variations-container" style="display: none;">
            <h5>Variasi</h5>
            <button type="button" id="add-variation" class="btn btn-primary mb-2">Tambahkan Variasi</button>
        </div>

        <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<style>
        .custom-modal-width {
            max-width: 90% !important; /* Override lebar maksimum Bootstrap */
            width: 1200px; /* Lebar spesifik */
        }
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
        #image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preview-container {
            position: relative;
            width: 150px;
        }

        .preview-image {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
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
    $(document).ready(function() {
    let allCategories = []; 
    let variations = []; // Data variasi dari API
    let selectedVariationTypes = []; // Tipe variasi yang dipilih
    let selectedVariationOptions = []; //Opsi variasi yang dipilih
    let selectedFiles = []; //menyimpan data gambar produk
    window.productVariations = [];
    
    const currencyFormat = new Intl.NumberFormat('id-ID');

    // Fungsi untuk memformat input mata uang
    function formatCurrencyInput(input) {
        let value = input.val().replace(/\D/g, ''); // Hanya angka
        let numericValue = parseInt(value, 10) || 0;

        // Update tampilan dengan format ribuan
        input.val(currencyFormat.format(numericValue));

        // Simpan nilai asli tanpa format di input hidden terkait
        input.siblings('input[type="hidden"]').val(numericValue);
    }

    // Fungsi validasi HPP & Harga
    function validatePriceInputs(row) {
        let hpp = parseInt(row.find('.hpp-hidden').val() || 0, 10);
        let price = parseInt(row.find('.price-hidden').val() || 0, 10);

        let hppInput = row.find('.hpp');
        let priceInput = row.find('.price');

        // Reset error sebelumnya
        hppInput.removeClass('is-invalid');
        priceInput.removeClass('is-invalid');
        row.find('.invalid-feedback').remove();

        if (hpp >= price) {
            hppInput.addClass('is-invalid');
            priceInput.addClass('is-invalid');
            priceInput.after('<div class="invalid-feedback">Harga harus lebih besar dari HPP</div>');
        }
    }
    $(document).on('input', '.currency', function() {
        formatCurrencyInput($(this));
    });

    // Event handler untuk validasi HPP & Harga
    $(document).on('input', '.hpp, .price', function() {
        let row = $(this).closest('#default-attributes');
        validatePriceInputs(row);
    });

    // Enhanced Initialization
    function initializeForm() {
        initializeSummernote();
        fetchVariations();
        // initializeVariationManagement();
        safeInitialize()
    }

    // Summernote Initialization with Enhanced Options
    function initializeSummernote() {
        $('#description').summernote({
            placeholder: "Masukan Deskripsi Detail",
            tabsize: 2,
            height: 150,
            callbacks: {
                onChange: function(contents, $editable) {
                    if ($(this).attr('id') === 'description') {
                        $('#detail_produk_deskripsi_detail').val(contents);
                    }
                }
            }
        });
    }

    // Enhanced Category Population
    function renderCategories(categories) {
        const mainCategoriesContainer = $('.main-categories');
        const subcategoriesPanel = $('#subcategories-panel');
        
        mainCategoriesContainer.empty();
        allCategories = categories;
        
        categories.forEach(category => {
            const categoryItem = $(`
                <div class="category-item" 
                     data-id="${category.id_kategori}" 
                     data-name="${category.nama_kategori}">
                    ${category.nama_kategori}
                    ${category.sub_kategori && category.sub_kategori.length > 0 
                        ? '<span class="float-right"><i class="fa fa-chevron-right"></i></span>' 
                        : ''}
                </div>
            `);
            
            // Hover event to show subcategories
            categoryItem.hover(
                function() {
                    $('.main-categories .category-item').removeClass('active');
                    $(this).addClass('active');
                    
                    subcategoriesPanel.empty();
                    
                    if (category.sub_kategori && category.sub_kategori.length > 0) {
                        category.sub_kategori.forEach(subCat => {
                            const subCategoryItem = $(`
                                <div class="subcategory-item" 
                                     data-id="${subCat.id_kategori}" 
                                     data-name="${subCat.nama_kategori}">
                                    ${subCat.nama_kategori}
                                </div>
                            `);
                            
                            // Click event for subcategory selection
                            subCategoryItem.on('click', function() {
                                selectCategory(
                                    subCat.id_kategori, 
                                    `${category.nama_kategori} - ${subCat.nama_kategori}`
                                );
                            });
                            
                            subcategoriesPanel.append(subCategoryItem);
                        });
                        
                        subcategoriesPanel.show();
                    } else {
                        subcategoriesPanel.hide();
                    }
                }
            );
            
            // Click event for main category selection (if no subcategories)
            categoryItem.on('click', function() {
                if (!category.sub_kategori || category.sub_kategori.length === 0) {
                    selectCategory(category.id_kategori, category.nama_kategori);
                }
            });
            
            mainCategoriesContainer.append(categoryItem);
        });
    }

    function selectCategory(id, name) {
        $('#cat_id').val(name);
        $('#selected_kategori_id').val(id);
        $('#category-dropdown').hide();
    }

    function filterCategories(searchTerm) {
        const mainCategoriesContainer = $('.main-categories');
        mainCategoriesContainer.empty();

        const filteredCategories = allCategories.filter(category => 
            category.nama_kategori.toLowerCase().includes(searchTerm.toLowerCase())
        );

        renderCategories(filteredCategories);
    }

    // Fetch and render categories
    $.ajax({
        url: `${getApiBaseUrl()}/api/kategori`,
        method: 'GET',
        success: function(response) {
            if (response.status) {
                renderCategories(response.data);
            }
        },
        error: function(xhr) {
            let errorMessage = "Gagal mengambil data kategori. Silakan coba lagi.";

            // Coba ambil pesan error dari response jika tersedia
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire({
                title: "Terjadi Kesalahan!",
                text: errorMessage,
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    });

    // Toggle category dropdown
    $('#category-dropdown-toggle, #cat_id').on('click', function() {
        $('#category-dropdown').toggle();
    });

    // Search functionality
    $('#category-search-input').on('input', function() {
        const searchTerm = $(this).val();
        filterCategories(searchTerm);
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group').length) {
            $('#category-dropdown').hide();
        }
    });

    // Fetch Variations with Error Handling
    function safeInitialize() {
        // Hapus event handler sebelumnya
        $('#productForm').off('submit');
        $('#has_variation').off('change');
        $('#add-variation').off('click');
        
        // Inisialisasi ulang event handler
        initializeFormEvents();
    }

    function initializeFormEvents() {
        // Toggle variasi
        $('#has_variation').on('change', function() {
            const hasVariation = this.checked;
            
            $('#variations-container').toggle(hasVariation);
            $('#default-attributes').toggle(!hasVariation);
            
            // Reset variasi saat di-uncheck
            if (!hasVariation) {
                window.productVariations = [];
            }
        });

        // Tombol tambah variasi
        $('#add-variation').on('click', function(e) {
            e.preventDefault(); // Mencegah submit form
            showVariationTypeModal();
        });

        // Submit form
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            handleFormSubmission.call(this, e);
            return false;
        });

        // Tambahan: pastikan tombol submit bisa diklik
        // $('button[type="submit"]').on('click', function(e) {
        //     e.preventDefault();
        //     $('#productForm').submit();
        // });
    }
    
    $('#gambar_produk').on('change', function() {
        const newFiles = Array.from(this.files); // Ambil semua file baru yang dipilih

        // Tambahkan file baru ke daftar
        selectedFiles = [...selectedFiles, ...newFiles];

        // Log semua file yang dipilih
        console.log('Gambar yang diunggah:');
        selectedFiles.forEach((file, index) => {
            console.log(`File ${index + 1}: ${file.name} (Ukuran: ${file.size} bytes, Tipe: ${file.type})`);
        });

        // Tampilkan pratinjau
        updateImagePreview();

        // Kosongkan input agar bisa memilih lagi
        $(this).val('');
    });

    // Fungsi untuk memperbarui pratinjau gambar
    function updateImagePreview() {
        const $previewContainer = $('#image-preview');
        $previewContainer.empty(); // Kosongkan pratinjau sebelumnya

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                const $preview = $(`
                    <div class="preview-container" data-index="${index}">
                        <img src="${e.target.result}" class="preview-image" alt="${file.name}">
                        <button class="remove-btn" data-index="${index}">X</button>
                    </div>
                `);
                $previewContainer.append($preview);
            };

            reader.readAsDataURL(file); // Baca file sebagai URL data untuk pratinjau
        });
    }

    // Event listener untuk tombol hapus
    $('#image-preview').on('click', '.remove-btn', function() {
        const index = $(this).data('index');
        selectedFiles.splice(index, 1); // Hapus file dari array

        // Perbarui pratinjau
        updateImagePreview();

        // Log ulang setelah penghapusan
        console.log('Gambar yang tersisa setelah penghapusan:');
        selectedFiles.forEach((file, index) => {
            console.log(`File ${index + 1}: ${file.name} (Ukuran: ${file.size} bytes, Tipe: ${file.type})`);
        });
    });

    // Integrasi dengan handleFormSubmission
    window.getSelectedFiles = function() {
        return selectedFiles; // Fungsi untuk mengakses file yang dipilih
    };

    function fetchVariations() {
        $.ajax({
            url: `${getApiBaseUrl()}/api/variasi-produk`, 
            method: 'GET',
            success: function(response) {
                if (response.status === "success" && response.data) {
                    variations = response.data;
                } else {
                    handleApiError('Failed to load variations');
                }
            },
            error: function(xhr) {
                handleApiError('Error fetching variations', xhr);
            }
        });
    }

    function showVariationTypeModal() {
    // // Hapus modal lama jika ada untuk mencegah duplikasi
    // $('#variationTypeModal').remove();
    
        let modalHtml = `
            <div class="modal fade" id="variationTypeModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pilih Tipe Variasi</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Tipe Tersimpan</h6>
                                    <div class="list-group" id="existingTypesContainer">
                                        ${renderExistingVariationTypes()}
                                    </div>
                                    <button class="btn btn-primary mb-3" id="showAddNewTypeModal">
                                        <i class="fas fa-plus"></i> Tambah Tipe
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="proceedToOptions" 
                                    ${selectedVariationTypes.length > 0 ? '' : 'disabled'}>
                                Pilih Opsi Variasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        
        // PENTING: Hapus event handler lama sebelum menambahkan yang baru
        $(document).off('change', '#existingTypesContainer .form-check-input');
        $('#showAddNewTypeModal').off('click');
        $('#proceedToOptions').off('click');
        
        // Tambahkan event handler baru
        $(document).on('change', '#existingTypesContainer .form-check-input', function() {
            const typeId = $(this).val();
            const typeName = $(this).next('label').text();
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                // Cek apakah tipe sudah ada di selectedVariationTypes
                if (!selectedVariationTypes.some(type => 
                    (type.id_tipe_variasi && type.id_tipe_variasi === typeId) || 
                    (type.nama_tipe === typeName)
                )) {
                    selectedVariationTypes.push({
                        id_tipe_variasi: typeId,
                        nama_tipe: typeName,
                        isNew: false
                    });
                }
            } else {
                // Hapus tipe dari selectedVariationTypes jika checkbox tidak dicentang
                selectedVariationTypes = selectedVariationTypes.filter(type => 
                    (type.id_tipe_variasi !== typeId && type.nama_tipe !== typeName)
                );
            }
            
            console.log(selectedVariationTypes);
            $('#proceedToOptions').prop('disabled', selectedVariationTypes.length === 0);
        });

        // Event untuk menampilkan modal tambah tipe baru
        $('#showAddNewTypeModal').on('click', function() {
            showAddNewTypeModal();
        });
        
        // Hapus modal opsi sebelumnya jika ada
        $('#variationOptionsModal').remove();
        
        // Lanjut ke pilihan opsi
        $('#proceedToOptions').on('click', showVariationOptionsModal);
        
        // Tampilkan modal
        $('#variationTypeModal').modal('show');
    }

    function renderExistingVariationTypes() {
        return variations.map(type => `
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="type_${type.id_tipe_variasi}" 
                    value="${type.id_tipe_variasi}" 
                    ${selectedVariationTypes.some(t => 
                        (t.id_tipe_variasi && t.id_tipe_variasi === type.id_tipe_variasi) || 
                        (t.nama_tipe === type.nama_tipe)
                    ) ? 'checked' : ''}>
                <label class="form-check-label" for="type_${type.id_tipe_variasi}">${type.nama_tipe}</label>
            </div>
        `).join('');
    }

    function showAddNewTypeModal() {
        // Hapus modal lama jika ada
        $('#addNewTypeModal').remove();
        
        const modalHtml = `
            <div class="modal fade" id="addNewTypeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Tipe Baru</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="newTypeName">Nama Tipe</label>
                                <input type="text" class="form-control" id="newTypeName" placeholder="Masukkan nama tipe">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="saveNewType">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        
        // Hapus event handler lama
        $('#saveNewType').off('click');
        
        // Tambahkan event handler baru
        $('#saveNewType').on('click', function() {
            const newTypeName = $('#newTypeName').val().trim();
            if (newTypeName) {
                // Cek apakah tipe sudah ada sebelumnya
                if (!selectedVariationTypes.some(type => type.nama_tipe === newTypeName)) {
                    // Tambahkan tipe baru ke selectedVariationTypes
                    selectedVariationTypes.push({
                        nama_tipe: newTypeName,
                        isNew: true
                    });

                    // Perbarui tampilan checkbox
                    $('#existingTypesContainer').append(`
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="type_new_${newTypeName}" 
                                value="new_${newTypeName}" checked>
                            <label class="form-check-label" for="type_new_${newTypeName}">${newTypeName}</label>
                        </div>
                    `);
                }

                // Reset input dan tutup modal
                $('#newTypeName').val('');
                $('#addNewTypeModal').modal('hide');
                $('#proceedToOptions').prop('disabled', false);
            }
        });
        
        // Tampilkan modal
        $('#addNewTypeModal').modal('show');
    }

    function showVariationOptionsModal() {
        $('#variationTypeModal').modal('hide');

        // Sinkronisasi selectedVariationOptions dengan selectedVariationTypes
        // Hapus opsi yang terkait dengan tipe yang tidak ada di selectedVariationTypes
        selectedVariationOptions = selectedVariationOptions.filter(option => 
            selectedVariationTypes.some(type => type.nama_tipe === option.nama_tipe)
        );

        // Hapus modal sebelumnya jika ada untuk menghindari duplikasi
        $('#variationOptionsModal').remove();
        $('#addOptionModal').remove();

        let modalHtml = `
            <div class="modal fade" id="variationOptionsModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pilih Opsi Variasi</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            ${renderVariationOptionsSelection()}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="proceedToDetails">
                                Masukan Data Variasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        $('#variationOptionsModal').modal('show');

        // Modal untuk menambah opsi variasi
        const addOptionModalHtml = `
            <div class="modal fade" id="addOptionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Opsi Baru</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Nama Opsi</label>
                                <input type="text" id="newOptionInput" class="form-control" 
                                    placeholder="Masukan Nama Opsi">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="saveNewOption">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(addOptionModalHtml);

        // Event untuk menambah opsi baru
        $(document).off('click', '.add-option-btn').on('click', '.add-option-btn', function() {
            const typeName = $(this).data('type-name');
            $('#addOptionModal').data('type-name', typeName);
            $('#addOptionModal').modal('show');
        });

        // Event untuk menyimpan opsi baru
        $(document).off('click', '#saveNewOption').on('click', '#saveNewOption', function() {
            const newOptionName = $('#newOptionInput').val().trim();
            const typeName = $('#addOptionModal').data('type-name');

            if (newOptionName) {
                // Temukan tipe yang sesuai
                const type = selectedVariationTypes.find(t => t.nama_tipe === typeName);
                
                if (type) {
                    // Buat ID unik untuk opsi baru
                    const newOptionId = `new_${Date.now()}`;

                    // Tambahkan opsi ke selectedVariationOptions
                    selectedVariationOptions.push({
                        id_tipe_variasi: type.id_tipe_variasi || null,
                        nama_tipe: type.nama_tipe,
                        id_opsi_variasi: newOptionId,
                        nama_opsi: newOptionName
                    });

                    // Perbarui tampilan
                    const existingOptionsContainer = $(`#existing-options-${typeName}`);
                    existingOptionsContainer.append(`
                        <div class="existing-option-item">
                            <input type="checkbox" class="existing-option" 
                                value="${newOptionId}" 
                                data-option-name="${newOptionName}"
                                data-type-name="${typeName}"
                                checked>
                            ${newOptionName}
                        </div>
                    `);

                    // Tambahkan juga opsi baru ke tipe yang sesuai jika belum ada property options
                    if (!type.options) {
                        type.options = [];
                    }
                    
                    // Tambahkan opsi baru ke tipe yang sesuai
                    type.options.push({
                        id_opsi_variasi: newOptionId,
                        nama_opsi: newOptionName
                    });

                    $('#newOptionInput').val('');
                    $('#addOptionModal').modal('hide');
                }
            }
        });

        // Event untuk memilih opsi (baik yang sudah ada maupun baru)
        $(document).off('change', '.existing-option').on('change', '.existing-option', function() {
            const optionId = $(this).val();
            const optionName = $(this).data('option-name');
            const typeName = $(this).closest('.existing-options-container').data('type-name');
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                // Temukan tipe yang sesuai
                const type = selectedVariationTypes.find(t => t.nama_tipe === typeName);
                
                if (type) {
                    // Cek apakah opsi ini sudah ada di selectedVariationOptions
                    const optionExists = selectedVariationOptions.some(opt => 
                        String(opt.id_opsi_variasi) === String(optionId) && opt.nama_tipe === typeName
                    );
                    
                    // Tambahkan opsi ke selectedVariationOptions jika belum ada
                    if (!optionExists) {
                        selectedVariationOptions.push({
                            id_tipe_variasi: type.id_tipe_variasi || null,
                            nama_tipe: type.nama_tipe,
                            id_opsi_variasi: optionId,
                            nama_opsi: optionName
                        });
                    }
                }
            } else {
                // Hapus opsi dari selectedVariationOptions
                selectedVariationOptions = selectedVariationOptions.filter(option => 
                    !(String(option.id_opsi_variasi) === String(optionId) && option.nama_tipe === typeName)
                );
            }

            console.log('selectedVariationOptions setelah perubahan:', selectedVariationOptions);
        });

        // Event untuk validasi dan lanjut ke detail
        $(document).off('click', '#proceedToDetails').on('click', '#proceedToDetails', function() {
            const allOptionsSelected = selectedVariationTypes.every(type => {
                return selectedVariationOptions.some(option => option.nama_tipe === type.nama_tipe);
            });

            if (allOptionsSelected) {
                showVariationDetailsModal(selectedVariationOptions);
            } else {
                Swal.fire({
                    title: "Terjadi Kesalahan!",
                    text: "Tolong pilih setidaknya satu opsi dari setiap variasi",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }

    function renderVariationOptionsSelection() {
        return selectedVariationTypes.map(type => `
            <div class="variation-options-section mb-4">
                <h6>${type.nama_tipe} Options</h6>
                <div class="row">
                    <div class="col-md-12">
                        <h7>Opsi Tersimpan</h7>
                        <div class="existing-options-container" 
                            id="existing-options-${type.nama_tipe}" 
                            data-type-name="${type.nama_tipe}">
                            ${renderExistingOptionsForType(type)}
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-primary add-option-btn mt-2" 
                        data-type-name="${type.nama_tipe}">
                    <i class="fas fa-plus"></i> Tambah Opsi Baru
                </button>
            </div>
        `).join('');
    }

    function renderExistingOptionsForType(type) {
        let optionsHtml = '';

        // Log untuk debugging
        console.log(`Rendering options for type: ${type.nama_tipe}`);
        console.log(`Current selectedVariationOptions:`, selectedVariationOptions);

        // Opsi yang sudah ada dari database
        if (type.id_tipe_variasi) {
            const typeVariations = variations.find(v => v.id_tipe_variasi == type.id_tipe_variasi);
            if (typeVariations && typeVariations.opsi_variasi) {
                optionsHtml += typeVariations.opsi_variasi.map(option => {
                    // Cek apakah opsi ini sudah dipilih sebelumnya
                    // PENTING: Convert ke string untuk perbandingan yang akurat
                    const isSelected = selectedVariationOptions.some(opt => 
                        String(opt.id_opsi_variasi) === String(option.id_opsi_variasi) && 
                        opt.nama_tipe === type.nama_tipe
                    );
                    
                    console.log(`Option ${option.nama_opsi} (${option.id_opsi_variasi}) for type ${type.nama_tipe} - Selected:`, isSelected);
                    console.log(`Type comparison: ${typeof option.id_opsi_variasi} vs ${typeof selectedVariationOptions[0]?.id_opsi_variasi}`);
                    
                    return `
                        <div class="existing-option-item">
                            <input type="checkbox" class="existing-option" 
                                value="${option.id_opsi_variasi}" 
                                data-option-name="${option.nama_opsi}"
                                data-type-name="${type.nama_tipe}"
                                ${isSelected ? 'checked' : ''}>
                            ${option.nama_opsi}
                        </div>
                    `;
                }).join('');
            }
        }

        // Opsi baru yang sudah ditambahkan
        if (type.options && type.options.length > 0) {
            optionsHtml += type.options.map(option => {
                // Cek apakah opsi ini sudah dipilih sebelumnya
                // PENTING: Convert ke string untuk perbandingan yang akurat
                const isSelected = selectedVariationOptions.some(opt => 
                    String(opt.id_opsi_variasi) === String(option.id_opsi_variasi) && 
                    opt.nama_tipe === type.nama_tipe
                );
                
                console.log(`New option ${option.nama_opsi} (${option.id_opsi_variasi}) for type ${type.nama_tipe} - Selected:`, isSelected);
                
                return `
                    <div class="existing-option-item">
                        <input type="checkbox" class="existing-option" 
                            value="${option.id_opsi_variasi}" 
                            data-option-name="${option.nama_opsi}"
                            data-type-name="${type.nama_tipe}"
                            ${isSelected ? 'checked' : ''}>
                        ${option.nama_opsi}
                    </div>
                `;
            }).join('');
        }

        return optionsHtml;
    }

    // Tambahkan fungsi ini untuk memperbaiki struktur selectedVariationOptions saat modal ditutup
    $(document).on('hide.bs.modal', '#variationOptionsModal', function() {
        // Pastikan semua opsi yang terpilih memiliki data yang lengkap
        selectedVariationOptions = selectedVariationOptions.map(option => {
            // Cari tipe yang sesuai
            const type = selectedVariationTypes.find(t => t.nama_tipe === option.nama_tipe);
            
            if (type) {
                // Cari opsi yang sesuai di variations
                if (type.id_tipe_variasi) {
                    const typeVariation = variations.find(v => v.id_tipe_variasi == type.id_tipe_variasi);
                    if (typeVariation && typeVariation.opsi_variasi) {
                        const existingOption = typeVariation.opsi_variasi.find(opt => 
                            String(opt.id_opsi_variasi) === String(option.id_opsi_variasi)
                        );
                        if (existingOption) {
                            // Lengkapi data dari existing option
                            return {
                                ...option,
                                id_tipe_variasi: type.id_tipe_variasi,
                                nama_opsi: existingOption.nama_opsi
                            };
                        }
                    }
                }
                
                // Cari di opsi baru
                if (type.options) {
                    const newOption = type.options.find(opt => 
                        String(opt.id_opsi_variasi) === String(option.id_opsi_variasi)
                    );
                    if (newOption) {
                        // Lengkapi data dari opsi baru
                        return {
                            ...option,
                            id_tipe_variasi: type.id_tipe_variasi || null,
                            nama_opsi: newOption.nama_opsi
                        };
                    }
                }
            }
            
            return option;
        });
        
        console.log('Updated selectedVariationOptions on modal close:', selectedVariationOptions);
    });

    function showVariationDetailsModal(selectedVariationOptions) {
        // Hide previous modal if it exists
        if ($('#variationOptionsModal').length) {
            $('#variationOptionsModal').modal('hide');
        }

        let modalHtml = `
            <div class="modal fade" id="variationDetailsModal" tabindex="-1" style="overflow-y: auto;">
                <div class="modal-dialog modal-xl custom-modal-width">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Variasi</h5>
                            <button type="button" class="close" data-dismiss="modal">×</button>
                        </div>
                        <div class="modal-body" id="variationDetailsContainer">
                            ${generateVariationDetailsContent(selectedVariationOptions)}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="saveVariationDetails">Simpan Variasi</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove previous modal if it exists
        $('#variationDetailsModal').remove();
        
        $('body').append(modalHtml);
        $('#variationDetailsModal').modal('show');

        // Event listener for save button
        $(document).off('click', '#saveVariationDetails').on('click', '#saveVariationDetails', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            saveVariationDetails();
            
            return false;
        });

        // Event listener for removing a variation
        $(document).off('click', '.remove-variation').on('click', '.remove-variation', function() {
            $(this).closest('.variation-detail-row').remove();
        });
    }
    function generateVariationDetailsContent(selectedVariationOptions) {
        if (!selectedVariationOptions || selectedVariationOptions.length === 0) {
            return '<p>Tidak ada variasi yang dipilih.</p>';
        }

        // Generate all possible combinations
        let combinations = generateVariationCombinations(selectedVariationOptions);
        console.log('pas',combinations);

        let tableHtml = `
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kombinasi Variasi</th>
                        <th>Stok</th>
                        <th>Berat (gram)</th>
                        <th>HPP</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    ${combinations.map((combination, index) => `
                        <tr class="variation-detail-row">
                            <td>
                                ${combination.label}
                                <input type="hidden" name="variations[${index}][combination]" value='${JSON.stringify(combination.options)}'>
                            </td>
                            <td>
                                <input type="number" class="form-control stock" 
                                    name="variations[${index}][stock]" 
                                    min="0" max="10000" required>
                            </td>
                            <td>
                                <input type="number" class="form-control weight" 
                                    name="variations[${index}][weight]" 
                                    min="0" max="100" step="0.01" required>
                            </td>
                            <td>
                                <input type="text" class="form-control hpp currency"  maxlength="11"
                                    name="variations[${index}][hpp]" 
                                    required>
                                <input type="hidden" class="hpp-hidden" name="variations[${index}][hpp_value]">
                            </td>
                            <td>
                                <input type="text" class="form-control price currency" maxlength="11"
                                    name="variations[${index}][price]" 
                                    required>
                                <input type="hidden" class="price-hidden" name="variations[${index}][price_value]">
                            </td>
                            <td>
                                <select class="form-control status" 
                                        name="variations[${index}][status]">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
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

        $(document).on('input', '.currency', function() {
            formatCurrencyInput($(this));
        });

        // Event handler untuk validasi HPP & Harga
        $(document).on('input', '.hpp, .price', function() {
            let row = $(this).closest('.variation-detail-row');
            validatePriceInputs(row);
        });


        return tableHtml;
    }

    function generateVariationCombinations(selectedVariationOptions) {
        // Group options by their type
        const optionsByType = {};
        
        selectedVariationOptions.forEach(option => {
            const typeKey = option.nama_tipe;
            
            if (!optionsByType[typeKey]) {
                optionsByType[typeKey] = [];
            }
            
            optionsByType[typeKey].push({
                id: option.id_opsi_variasi,
                name: option.nama_opsi,
                typeId: option.id_tipe_variasi
            });
        });
        
        // Start with an empty combination
        let combinations = [{ label: '', options: [] }];
        
        // For each type, create new combinations with all its options
        Object.keys(optionsByType).forEach(typeName => {
            const typeOptions = optionsByType[typeName];
            let newCombinations = [];
            
            // For each existing combination, add each option of the current type
            combinations.forEach(combo => {
                typeOptions.forEach(option => {
                    newCombinations.push({
                        label: combo.label ? 
                            `${combo.label} - ${option.name}` : 
                            option.name,
                        options: [...combo.options, {
                            id: option.id,
                            name: option.name,
                            type: typeName,
                            typeId: option.typeId
                        }]
                    });
                });
            });
            
            combinations = newCombinations;
        });
        
        return combinations;
    }

    function validateMainForm() {
        let isValid = true;

        // Reset previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Validasi nama produk
        const $namaProduk = $('#nama_produk');
        if (!$namaProduk.val().trim()) {
            $namaProduk.addClass('is-invalid');
            $namaProduk.after('<div class="invalid-feedback">Nama produk harus diisi</div>');
            isValid = false;
        }

        // Validasi kategori
        const $kategori = $('#cat_id');
        const $kategoriId = $('#selected_kategori_id');
        if (!$kategori.val().trim() || !$kategoriId.val()) {
            $kategori.addClass('is-invalid');
            $kategori.after('<div class="invalid-feedback">Kategori harus dipilih</div>');
            isValid = false;
        }

        // Validasi gambar produk (minimal satu gambar wajib ada)
        const selectedFiles = window.getSelectedFiles();
        if (selectedFiles.length === 0) {
            $('#gambar_produk').addClass('is-invalid');
            $('#gambar_produk').after('<div class="invalid-feedback">Minimal satu gambar produk harus diunggah</div>');
            isValid = false;
        }

        return isValid;
    }

    function validateProductAttributes($container) {
        let isValid = true;
        let attributes = {};

        // Reset error styles
        $container.find('.is-invalid').removeClass('is-invalid');
        $container.find('.invalid-feedback').remove();

        const $stockInput = $container.find('.stock');
        const $weightInput = $container.find('.weight');
        const $hppInput = $container.find('.hpp');
        const $priceInput = $container.find('.price');
        const $statusSelect = $container.find('.status');

        // Cek jika form kosong
        if (!$stockInput.length || !$weightInput.length || !$hppInput.length || !$priceInput.length || !$statusSelect.length) {
            $container.append('<div class="invalid-feedback">Semua field atribut produk harus ada</div>');
            return { isValid: false, attributes };
        }

        // Konversi nilai
        const stock = parseInt($stockInput.val(), 10);
        const weight = parseFloat($weightInput.val());
        const hpp = parseInt($container.find('.hpp-hidden').val() || 0, 10);
        const price = parseInt($container.find('.price-hidden').val() || 0, 10);

        // Validasi stok
        if (isNaN(stock) || stock < 0 || stock > 10000) {
            $stockInput.addClass('is-invalid');
            let errorMsg = stock > 10000 ? 'Stok tidak boleh lebih dari 10.000' : 'Stok harus berupa bilangan bulat positif';
            $stockInput.after(`<div class="invalid-feedback">${errorMsg}</div>`);
            isValid = false;
        }

        // Validasi berat
        if (isNaN(weight) || weight < 0 || weight > 100) {
            $weightInput.addClass('is-invalid');
            let errorMsg = weight > 100 ? 'Berat tidak boleh lebih dari 100' : 'Berat harus berupa angka positif';
            $weightInput.after(`<div class="invalid-feedback">${errorMsg}</div>`);
            isValid = false;
        }

        // Validasi HPP
        if (isNaN(hpp) || hpp < 0) {
            $hppInput.addClass('is-invalid');
            $hppInput.after('<div class="invalid-feedback">HPP harus berupa angka positif</div>');
            isValid = false;
        }

        // Validasi Harga
        if (isNaN(price) || price < 0) {
            $priceInput.addClass('is-invalid');
            $priceInput.after('<div class="invalid-feedback">Harga harus berupa angka positif</div>');
            isValid = false;
        }

        // Validasi HPP < Harga
        if (hpp >= price) {
            $hppInput.addClass('is-invalid');
            $priceInput.addClass('is-invalid');
            $priceInput.after('<div class="invalid-feedback">Harga harus lebih besar dari HPP</div>');
            isValid = false;
        }

        // Validasi Status
        if (!$statusSelect.val()) {
            $statusSelect.addClass('is-invalid');
            $statusSelect.after('<div class="invalid-feedback">Status harus dipilih</div>');
            isValid = false;
        }

        if (isValid) {
            attributes = {
                stok: stock,
                berat: weight.toFixed(2),
                hpp: hpp.toFixed(2),
                harga: price.toFixed(2),
                status: $statusSelect.val()
            };
        }

        return { isValid, attributes };
    }

    function saveVariationDetails() {
        try {
            let variations = [];
            let allValid = true;

            $('.variation-detail-row').each(function(index) {
                const $this = $(this);
                const { isValid, attributes } = validateProductAttributes($this);

                if (!isValid) {
                    allValid = false;
                    return; // Lanjut ke iterasi berikutnya
                }

                const $imageInput = $this.find('.variation-image');
                const combinationJSON = $this.find('input[name^="variations"][name$="[combination]"]').val();
                const combinationOptions = JSON.parse(combinationJSON);

                // Format tipe_variasi
                const typeGroups = {};
                combinationOptions.forEach(option => {
                    if (!typeGroups[option.type]) {
                        typeGroups[option.type] = {
                            typeData: { nama_tipe: option.type },
                            options: []
                        };
                        if (option.typeId && option.typeId !== 'null' && !option.typeId.toString().startsWith('new_')) {
                            typeGroups[option.type].typeData.id_tipe_variasi = option.typeId;
                        }
                    }
                    const optionData = { nama_opsi: option.name };
                    if (option.id && option.id !== 'null' && !option.id.toString().startsWith('new_')) {
                        optionData.id_opsi_variasi = option.id;
                    }
                    typeGroups[option.type].options.push(optionData);
                });

                const tipe_variasi = Object.values(typeGroups).map(group => ({
                    ...group.typeData,
                    opsi: group.options
                }));

                const variationData = {
                    kombinasi: $this.find('td:first').text().trim(),
                    ...attributes,
                    gambar: $imageInput[0].files[0] || null, // Gambar variasi opsional
                    tipe_variasi: tipe_variasi
                };

                variations.push(variationData);
            });

            if (!allValid) {
                Swal.fire({
                    title: 'Validasi Gagal',
                    text: 'Harap lengkapi semua field dengan benar',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            window.productVariations = variations;
            $('#variationDetailsModal').modal('hide');

            Swal.fire({
                title: 'Variasi Tersimpan',
                text: `${variations.length} variasi telah disimpan`,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            $('#add-variation').text('Edit Variasi Tersimpan').removeClass('btn-primary').addClass('btn-warning');
            return true;
        } catch (error) {
            console.error('Error in saveVariationDetails:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan saat menyimpan variasi',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }
    }

    function handleFormSubmission(e) {
        e.preventDefault();

        const submitButton = $('#productForm button[type="submit"]');
        if (submitButton.prop('disabled')) return; // Cegah klik berulang

        submitButton.prop('disabled', true);
        const originalButtonText = submitButton.html();
        submitButton.html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
        
        // Reset previous error styles
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validasi form utama
        if (!validateMainForm()) {
            return false;
        }

        const formData = new FormData(this);
       
        // Cek apakah produk memiliki variasi
        const hasVariation = $('#has_variation').is(':checked');

        const selectedFiles = window.getSelectedFiles();
        if (selectedFiles.length === 0) {
            Swal.fire({
                title: 'Error!',
                text: 'Minimal satu gambar produk harus diunggah',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }

        selectedFiles.forEach((file, index) => {
            formData.append(`gambar_produk[${index}]`, file);
        });
        
        try {
            // Proses variasi jika ada
            if (hasVariation) {
                // Pastikan variasi sudah disimpan
                if (!window.productVariations || window.productVariations.length === 0) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Harap isi detail variasi terlebih dahulu',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Tambahkan variasi ke FormData dengan format yang sesuai backend
                window.productVariations.forEach((variation, varIndex) => {
                    // Append basic variation data
                    formData.append(`variasi[${varIndex}][stok]`, String(variation.stok));
                    formData.append(`variasi[${varIndex}][berat]`, String(variation.berat));
                    formData.append(`variasi[${varIndex}][hpp]`, String(variation.hpp));
                    formData.append(`variasi[${varIndex}][harga]`, String(variation.harga));
                    formData.append(`variasi[${varIndex}][status]`, variation.status);

                    // Append image if exists
                    if (variation.gambar) {
                        formData.append(`variasi[${varIndex}][gambar][]`, variation.gambar);
                    }

                    // Append variation types and options
                    variation.tipe_variasi.forEach((tipe, typeIndex) => {
                        // Append type data
                        if (tipe.id_tipe_variasi) {
                            formData.append(`variasi[${varIndex}][tipe_variasi][${typeIndex}][id_tipe_variasi]`, 
                                tipe.id_tipe_variasi);
                        }
                        
                        formData.append(`variasi[${varIndex}][tipe_variasi][${typeIndex}][nama_tipe]`, 
                            tipe.nama_tipe);
                        
                        // Append option data
                        if (tipe.opsi.id_opsi_variasi) {
                            formData.append(`variasi[${varIndex}][tipe_variasi][${typeIndex}][opsi][id_opsi_variasi]`, 
                                tipe.opsi.id_opsi_variasi);
                        }
                        
                        formData.append(`variasi[${varIndex}][tipe_variasi][${typeIndex}][opsi][nama_opsi]`, 
                            tipe.opsi.nama_opsi);
                    });
                });
            
                // Tambahkan flag bahwa produk memiliki variasi
                formData.append('has_variation', '1');

            } else {
                const $defaultContainer = $('#default-attributes');
                const { isValid, attributes } = validateProductAttributes($defaultContainer);

                if (!isValid) {
                    Swal.fire({
                        title: 'Validasi Gagal',
                        text: 'Harap lengkapi semua field atribut produk dengan benar',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Langsung append karena sudah divalidasi
                formData.append('stok', String(attributes.stok));
                formData.append('berat', String(attributes.berat));
                formData.append('hpp', String(attributes.hpp));
                formData.append('harga', String(attributes.harga));
                formData.append('status', attributes.status);

                formData.append('has_variation', '0');
            }
           
            // Kirim data
            $.ajax({
                url: `${getApiBaseUrl()}/api/produk/tambah`,
                method: 'POST',
                headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Authorization': `Bearer ${getJwtToken()}`
                    },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Sukses!',
                            text: 'Produk berhasil ditambahkan',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reset form
                            $('#productForm')[0].reset();
                            $('#variations-container').hide();
                            $('#default-attributes').show();
                            
                            // Hapus variasi yang tersimpan
                            window.productVariations = [];
                        });
                    } else {
                        // Tangani error dari backend
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Gagal menambah produk',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    // Tangani error validasi
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        
                        // Tampilkan error validasi
                        Object.keys(errors).forEach(field => {
                            // Cari input berdasarkan nama field
                            const $input = $(`[name="${field}"], #${field}`);
                            
                            if ($input.length) {
                                $input.addClass('is-invalid');
                                
                                // Tambahkan pesan error
                                const errorMessage = errors[field][0];
                                const $errorDiv = $('<div>').addClass('invalid-feedback').text(errorMessage);
                                $input.after($errorDiv);
                            }
                        });

                        Swal.fire({
                            title: 'Validasi Gagal',
                            text: 'Silakan periksa kembali input Anda',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Tangani error lainnya
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat mengirim data',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    
                    console.error(xhr);
                },
                complete: function() {
                    submitButton.prop('disabled', false).attr('disabled', false).html('Submit');
                }
            });
        } catch (error) {
            console.error('Error in form submission:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan dalam proses submit',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }

        // Mencegah submit default form
        return false;
    }

    // Tambahkan event listener yang aman
    $(document).on('click', '#saveVariationDetails', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            saveVariationDetails();
        } catch (error) {
            console.error('Error in save variations event:', error);
        }
        
        return false;
    });
   

    // Centralized Error Handling
    function handleApiError(message, details = {}) {
        console.error(message, details);
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }

    // Initialize Form
    initializeForm();
});
</script>
@endpush
@endsection