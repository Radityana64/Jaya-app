@extends('backend.layouts.master')

@section('main-content')
<div class="container mt-5">
    <h2>Add Product</h2>
    <form id="productForm" enctype="multipart/form-data">
        @csrf
        <div class="form-group position-relative">
          <label for="cat_id">Category <span class="text-danger">*</span></label>
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
            <label for="nama_produk">Product Name <span class="text-danger">*</span></label>
            <input id="nama_produk" type="text" name="nama_produk" placeholder="Enter product name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Short Description<span class="text-danger">*</span></label>
            <input id="deskripsi" type="text" name="deskripsi" placeholder="Enter Short Description" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Detail Description</label>
            <div id="description"></div>
            <input type="hidden" name="detail_produk[deskripsi_detail]" id="detail_produk_deskripsi_detail">
        </div>

        <div class="form-group">
            <label for="detail_produk.url_video">Video URL</label>
            <input id="url_video" type="url" name="detail_produk[url_video]" placeholder="Enter video URL" class="form-control">
        </div>

        <div class="form-group">
            <label for="gambar_produk">Product Images</label>
            <input id="gambar_produk" type="file" name="gambar_produk[]" multiple class="form-control">
        </div>

        <div class="form-group">
            <label for="has_variation">Has Variations</label>
            <input type="checkbox" id="has_variation" name="has_variation">
        </div>

         <div id="default-attributes" class="mb-3"> 
            <h5>Default Attributes</h5>
            <div class="form-group">
                <label for="stok">Stock</label>
                <input type="number" name="stok" id="stok" class="form-control" min="0" required>
            </div>
            <div class="form-group">
                <label for="berat">Weight</label>
                <input type="number" name="berat" id="berat" class="form-control" min="0" required>
            </div>
            <div class="form-group">
                <label for="hpp">HPP</label>
                <input type="number" name="hpp" id="hpp" class="form-control" min="0" required>
            </div>
            <div class="form-group">
                <label for="harga">Price</label>
                <input type="number" name="harga" id="harga" class="form-control" min="0" required>
            </div>
            <div class="form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="aktif">Active</option>
                    <option value="nonaktif">Inactive</option>
                </select>
            </div>
        </div> 

        <div id="variations-container" style="display: none;">
            <h5>Variations</h5>
            <button type="button" id="add-variation" class="btn btn-primary">Add Variation</button>
        </div>

        <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>

@push('styles')
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
        
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
    $(document).ready(function() {
    let allCategories = []; 
    let variations = []; // Data variasi dari API
    let selectedVariationTypes = []; // Tipe variasi yang dipilih
    let selectedVariationOptions = [];
    window.productVariations = [];

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
            placeholder: "Write description.....",
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
        url: 'http://127.0.0.1:8000/api/kategori',
        method: 'GET',
        success: function(response) {
            if (response.status) {
                renderCategories(response.data);
            }
        },
        error: function() {
            alert('Failed to load categories');
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
        $('button[type="submit"]').on('click', function(e) {
            e.preventDefault();
            $('#productForm').submit();
        });
    }

function fetchVariations() {
    $.ajax({
        url: 'http://127.0.0.1:8000/api/variasi-produk', 
        method: 'GET',
        dataType: 'json',
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
    let modalHtml = `
        <div class="modal fade" id="variationTypeModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select Variation Types</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Existing Types</h6>
                                <div class="list-group" id="existingTypesContainer">
                                    ${renderExistingVariationTypes()}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Add New Type</h6>
                                <div class="input-group mb-3">
                                    <input type="text" id="newVariationType" class="form-control" 
                                           placeholder="Enter new variation type">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" id="addNewVariationType">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <h6>Selected Types</h6>
                                <div id="selectedTypesContainer" class="list-group">
                                    <!-- Akan diisi secara dinamis -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="proceedToOptions" 
                                ${selectedVariationTypes.length > 0 ? '' : 'disabled'}>
                            Next: Select Options
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('body').append(modalHtml);
    $('#variationTypeModal').modal('show');

    // Event untuk memilih tipe variasi dari existing
    $(document).on('click', '#existingTypesContainer .list-group-item', function() {
        const typeId = $(this).data('type-id');
        const typeName = $(this).data('type-name');
        
        // Cegah duplikasi
        if (!selectedVariationTypes.some(type => 
            (type.id_tipe_variasi && type.id_tipe_variasi === typeId) || 
            (type.nama_tipe === typeName)
        )) {
            selectedVariationTypes.push({
                id_tipe_variasi: typeId,
                nama_tipe: typeName,
                isNew: false
            });
            
            updateSelectedTypesDisplay();
            $('#proceedToOptions').prop('disabled', false);
        }
    });

    // Event untuk menambah tipe variasi baru
    $('#addNewVariationType').on('click', function() {
        const newType = $('#newVariationType').val().trim();
        if (newType) {
            selectedVariationTypes.push({
                nama_tipe: newType,
                isNew: true
            });
            
            updateSelectedTypesDisplay();
            $('#newVariationType').val('');
            $('#proceedToOptions').prop('disabled', false);
        }
    });

    // Event untuk menghapus tipe variasi yang dipilih
    $(document).on('click', '.remove-selected-type', function() {
        const typeName = $(this).data('type-name');
        selectedVariationTypes = selectedVariationTypes.filter(type => 
            type.nama_tipe !== typeName
        );
        updateSelectedTypesDisplay();
        
        // Disable next button jika tidak ada tipe yang dipilih
        if (selectedVariationTypes.length === 0) {
            $('#proceedToOptions').prop('disabled', true);
        }
    });

    // Lanjut ke pilihan opsi
    $('#proceedToOptions').on('click', showVariationOptionsModal);
}

function renderExistingVariationTypes() {
    return variations.map(type => `
        <a href="#" class="list-group-item list-group-item-action" 
           data-type-id="${type.id_tipe_variasi}" 
           data-type-name="${type.nama_tipe}">
            ${type.nama_tipe}
        </a>
    `).join('');
}

function updateSelectedTypesDisplay() {
    const container = $('#selectedTypesContainer');
    container.empty();

    selectedVariationTypes.forEach(type => {
        container.append(`
            <div class="list-group-item d-flex justify-content-between align-items-center">
                ${type.nama_tipe}
                <button class="btn btn-sm btn-danger remove-selected-type" 
                        data-type-name="${type.nama_tipe}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
    });
}

function showVariationOptionsModal() {
    $('#variationTypeModal').modal('hide');

    let modalHtml = `
        <div class="modal fade" id="variationOptionsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Variation Options Management</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${renderVariationOptionsSelection()}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="proceedToDetails">
                            Next: Enter Variation Details
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
                        <h5 class="modal-title">Add New Option</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Option Name</label>
                            <input type="text" id="newOptionInput" class="form-control" 
                                   placeholder="Enter option name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveNewOption">Save</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('body').append(addOptionModalHtml);

    // Inisialisasi penyimpanan opsi untuk tipe baru
    selectedVariationTypes.forEach(type => {
        if (type.isNew && !type.options) {
            type.options = [];
        }
    });

    // Event untuk menambah opsi baru
    $(document).on('click', '.add-option-btn', function() {
        const typeName = $(this).data('type-name');
        
        $('#addOptionModal').data('type-name', typeName);
        $('#addOptionModal').modal('show');
    });

    // Event untuk menyimpan opsi baru
    $(document).on('click', '#saveNewOption', function() {
        const newOptionName = $('#newOptionInput').val().trim();
        const typeName = $('#addOptionModal').data('type-name');

        if (newOptionName) {
            // Temukan tipe yang sesuai
            const typeIndex = selectedVariationTypes.findIndex(type => type.nama_tipe === typeName);
            
            if (typeIndex !== -1) {
                // Tambahkan opsi ke tipe
                if (!selectedVariationTypes[typeIndex].options) {
                    selectedVariationTypes[typeIndex].options = [];
                }
                selectedVariationTypes[typeIndex].options.push(newOptionName);

                // Perbarui tampilan
                const selectedOptionsContainer = $(`#selected-options-${typeName}`);
                selectedOptionsContainer.append(`
                    <div class="selected-option-item">
                        <span>${newOptionName}</span>
                        <button class="btn btn-sm btn-danger remove-selected-option">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);

                $('#newOptionInput').val('');
                $('#addOptionModal').modal('hide');
            }
        }
    });

    // Event untuk menyimpan opsi
    $('#proceedToDetails').on('click', function() {
        const allOptionsSelected = selectedVariationTypes.every(type => {
            // Untuk tipe yang sudah ada
            if (type.id_tipe_variasi) {
                const existingOptions = $(`.existing-options-container[data-type-id="${type.id_tipe_variasi}"] .existing-option:checked`).length;
                return existingOptions > 0;
            }
            // Untuk tipe baru
            return type.options && type.options.length > 0;
        });

        if (allOptionsSelected) {
            showVariationDetailsModal();
        } else {
            alert('Please select at least one option for each variation type.');
        }
    });
}

function renderVariationOptionsSelection() {
    return selectedVariationTypes.map(type => `
        <div class="variation-options-section mb-4">
            <h6>${type.nama_tipe} Options</h6>
            <div class="row">
                <div class="col-md-6">
                    <h7>Existing Options</h7>
                    <div class="existing-options-container" 
                         data-type-id="${type.id_tipe_variasi || '-'}" 
                         data-type-name="${type.nama_tipe}">
                        ${type.id_tipe_variasi ? renderExistingOptionsForType(type) : '-'}
                    </div>
                </div>
                <div class="col-md-6">
                    <h7>Selected Options</h7>
                    <div class="selected-options-container" 
                         id="selected-options-${type.nama_tipe}">
                        ${renderSelectedOptionsForType(type)}
                    </div>
                </div>
            </div>
            <button class="btn btn-sm btn-primary add-option-btn mt-2" 
                    data-type-name="${type.nama_tipe}">
                <i class="fas fa-plus"></i> Add New Option
            </button>
        </div>
    `).join('');
}

function renderSelectedOptionsForType(type) {
    // Untuk tipe yang sudah ada atau baru
    if (type.options && type.options.length > 0) {
        return type.options.map((option, index) => `
            <div class="selected-option-item d-flex justify-content-between align-items-center mb-2">
                <span>${option}</span>
                <button class="btn btn-sm btn-danger remove-selected-option" 
                        data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }
    return '';
}

function renderExistingOptionsForType(type) {
    if (type.id_tipe_variasi) {
        const typeVariations = variations.find(v => v.id_tipe_variasi == type.id_tipe_variasi);
        return typeVariations ? typeVariations.opsi_variasi.map(option => 
            `<div class="existing-option-item">
                <input type="checkbox" class="existing-option" 
                       value="${option.id_opsi_variasi}" 
                       data-option-name="${option.nama_opsi}">
                ${option.nama_opsi}
            </div>`
        ).join('') : '-';
    }
    return '-';
}

$(document).on('click', '.remove-selected-option', function() {
    $(this).closest('.selected-option-item').remove();
});

function showVariationDetailsModal() {
    $('#variationOptionsModal').modal('hide');

    let variationSelections = selectedVariationTypes.map(type => {
        let existingOptions = [];
        let newOptions = [];

        // Untuk tipe yang sudah ada
        if (type.id_tipe_variasi) {
            existingOptions = $(`.existing-options-container[data-type-id="${type.id_tipe_variasi}"] .existing-option:checked`)
                .map(function() { return $(this).val(); }).get();
        }
        
        // Untuk tipe baru
        if (type.options) {
            newOptions = type.options;
        }

        return {
            typeName: type.nama_tipe,
            existingOptions,
            newOptions
        };
    });

    let modalHtml = `
        <div class="modal fade" id="variationDetailsModal" tabindex="-1" style="overflow-y: auto;">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Variation Details</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="variationDetailsContainer">
                        ${generateVariationDetailsContent(variationSelections)}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveVariationDetails">Save Variations</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Hapus modal sebelumnya jika ada
    $('#variationDetailsModal').remove();
    
    $('body').append(modalHtml);
    $('#variationDetailsModal').modal('show');

    // Event listener untuk tombol save (gunakan delegasi event)
    $(document).off('click', '#saveVariationDetails').on('click', '#saveVariationDetails', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        saveVariationDetails();
        
        return false;
    });

    // Event listener untuk menghapus variasi
    $(document).off('click', '.remove-variation').on('click', '.remove-variation', function() {
        $(this).closest('.variation-detail-row').remove();
    });
}

function generateVariationDetailsContent(variationSelections) {
    if (!variationSelections || variationSelections.length === 0) {
        return '<p>No variations selected.</p>';
    }

    let combinations = generateVariationCombinations(variationSelections);

    let tableHtml = `
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Variation Combination</th>
                    <th>Stock</th>
                    <th>Weight</th>
                    <th>HPP</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${combinations.map((combination, index) => `
                    <tr class="variation-detail-row">
                        <td>${combination.label}</td>
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
                            <input type="number" class="form-control hpp" 
                                   name="variations[${index}][hpp]" 
                                   min="0" step="0.01" required>
                        </td>
                        <td>
                            <input type="number" class="form-control price" 
                                   name="variations[${index}][price]" 
                                   min="0" step="0.01" required>
                        </td>
                        <td>
                            <select class="form-control status" 
                                    name="variations[${index}][status]">
                                <option value="aktif">Active</option>
                                <option value="nonaktif">Inactive</option>
                            </select>
                        </td>
                        <td>
                            <input type="file" class="form-control variation-image" 
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

    return tableHtml;
}

function generateVariationCombinations(variationSelections) {
    let combinations = [{ label: '', options: [] }];

    variationSelections.forEach(selection => {
        let newCombinations = [];

        let allOptions = [
            ...selection.existingOptions.map(id => ({ 
                id, 
                name: findOptionNameById(selection.typeName, id) 
            })),
            ...selection.newOptions.map(name => ({ 
                id: null, 
                name 
            }))
        ];

        combinations.forEach(combo => {
            allOptions.forEach(option => {
                newCombinations.push({
                    label: combo.label ? 
                        `${combo.label} - ${option.name}` : 
                        option.name,
                    options: [...(combo.options || []), option]
                });
            });
        });

        combinations = newCombinations;
    });

    return combinations;
}

function findOptionNameById(typeName, optionId) {
    const type = variations.find(v => v.nama_tipe === typeName);
    if (type) {
        const option = type.opsi_variasi.find(o => o.id_opsi_variasi == optionId);
        return option ? option.nama_opsi : optionId;
    }
    return optionId;
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

        // Validasi status
        const $status = $('select[name="status"]');
        if (!$status.val()) {
            $status.addClass('is-invalid');
            $status.after('<div class="invalid-feedback">Status harus dipilih</div>');
            isValid = false;
        }

        return isValid;
    }

    function saveVariationDetails() {
    try {
        let isValid = true;
        let variations = [];

        $('.variation-detail-row').each(function(index) {
            // Reset error styles
            $(this).find('.is-invalid').removeClass('is-invalid');
            
            // Validasi input
            const $stockInput = $(this).find('.stock');
            const $weightInput = $(this).find('.weight');
            const $hppInput = $(this).find('.hpp');
            const $priceInput = $(this).find('.price');
            const $statusSelect = $(this).find('.status');
            const $imageInput = $(this).find('.variation-image');

            // Konversi dan validasi input
            const stock = parseInt($stockInput.val(), 10);
            const weight = parseFloat($weightInput.val());
            const hpp = parseFloat($hppInput.val());
            const price = parseFloat($priceInput.val());

            // Cek validasi dengan pesan error yang lebih spesifik
            if (isNaN(stock) || stock < 0) {
                $stockInput.addClass('is-invalid');
                $stockInput.after('<div class="invalid-feedback">Stok harus berupa bilangan bulat positif</div>');
                isValid = false;
            }
            if (isNaN(weight) || weight < 0) {
                $weightInput.addClass('is-invalid');
                $weightInput.after('<div class="invalid-feedback">Berat harus berupa angka positif</div>');
                isValid = false;
            }
            if (isNaN(hpp) || hpp < 0) {
                $hppInput.addClass('is-invalid');
                $hppInput.after('<div class="invalid-feedback">HPP harus berupa angka positif</div>');
                isValid = false;
            }
            if (isNaN(price) || price < 0) {
                $priceInput.addClass('is-invalid');
                $priceInput.after('<div class="invalid-feedback">Harga harus berupa angka positif</div>');
                isValid = false;
            }

            // Kumpulkan data variasi dengan tipe data yang benar
            const variationData = {
                kombinasi: $(this).find('td:first').text(),
                stok: stock,
                berat: weight.toFixed(2), // Pastikan 2 desimal
                hpp: hpp.toFixed(2),
                harga: price.toFixed(2),
                status: $statusSelect.val(),
                gambar: $imageInput[0].files[0] || null
            };

            variations.push(variationData);
        });

        // Jika tidak valid, tampilkan pesan
        if (!isValid) {
            Swal.fire({
                title: 'Validasi Gagal',
                text: 'Harap lengkapi semua field dengan benar',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }

        // Simpan variasi ke variabel global
        window.productVariations = variations;

        // Tutup modal
        $('#variationDetailsModal').modal('hide');

        // Tampilkan konfirmasi
        Swal.fire({
            title: 'Variasi Tersimpan',
            text: `${variations.length} variasi telah disimpan`,
            icon: 'success',
            confirmButtonText: 'OK'
        });

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
                // Tambahkan field dasar variasi dengan konversi tipe data eksplisit
                formData.append(`variasi[${varIndex}][stok]`, String(variation.stok));
                formData.append(`variasi[${varIndex}][berat]`, String(variation.berat));
                formData.append(`variasi[${varIndex}][hpp]`, String(variation.hpp));
                formData.append(`variasi[${varIndex}][harga]`, String(variation.harga));
                formData.append(`variasi[${varIndex}][status]`, variation.status);

                // Tambahkan gambar variasi jika ada
                if (variation.gambar) {
                    formData.append(`variasi[${varIndex}][gambar][]`, variation.gambar);
                }

                // Tambahkan tipe dan opsi variasi
                const kombinasiParts = variation.kombinasi.split(' - ');
                kombinasiParts.forEach((part, index) => {
                    // Pastikan menggunakan tipe variasi yang dipilih sebelumnya
                    if (selectedVariationTypes[index]) {
                        formData.append(`variasi[${varIndex}][tipe_variasi][${index}][nama_tipe]`, 
                            selectedVariationTypes[index].nama_tipe);
                        formData.append(`variasi[${varIndex}][tipe_variasi][${index}][opsi][nama_opsi]`, part);
                    }
                });
            });

            // Tambahkan flag bahwa produk memiliki variasi
            formData.append('has_variation', '1');
        } else {
            // Untuk produk tanpa variasi, gunakan input default dengan konversi tipe data
            const defaultFields = [
                { name: 'stok', selector: '#stok', type: 'int' },
                { name: 'berat', selector: '#berat', type: 'float' },
                { name: 'hpp', selector: '#hpp', type: 'float' },
                { name: 'harga', selector: '#harga', type: 'float' },
                { name: 'status', selector: 'select[name="status"]', type: 'string' }
            ];
            
            let isDefaultValid = true;
            defaultFields.forEach(field => {
                const $input = $(field.selector);
                const rawValue = $input.val();
                let processedValue;

                // Konversi tipe data sesuai kebutuhan
                switch(field.type) {
                    case 'int':
                        processedValue = parseInt(rawValue, 10);
                        if (isNaN(processedValue) || processedValue < 0) {
                            $input.addClass('is-invalid');
                            $input.after(`<div class="invalid-feedback">${field.name.charAt(0).toUpperCase() + field.name.slice(1)} harus diisi dan harus berupa bilangan bulat positif</div>`);
                            isDefaultValid = false;
                        }
                        break;
                    case 'float':
                        processedValue = parseFloat(rawValue);
                        if (isNaN(processedValue) || processedValue < 0) {
                            $input.addClass('is-invalid');
                            $input.after(`<div class="invalid-feedback">${field.name.charAt(0).toUpperCase() + field.name.slice(1)} harus diisi dan harus berupa angka positif</div>`);
                            isDefaultValid = false;
                        }
                        break;
                    case 'string':
                        processedValue = rawValue;
                        if (!processedValue) {
                            $input.addClass('is-invalid');
                            $input.after(`<div class="invalid-feedback">${field.name.charAt(0).toUpperCase() + field.name.slice(1)} harus diisi</div>`);
                            isDefaultValid = false;
                        }
                        break;
                }

                // Tambahkan nilai yang sudah diproses ke FormData
                if (isDefaultValid) {
                    formData.append(field.name, processedValue);
                }
            });

            // Jika validasi default gagal, hentikan proses
            if (!isDefaultValid) {
                return false;
            }

            // Tambahkan flag bahwa produk tidak memiliki variasi
            formData.append('has_variation', '0');
        }

        // Kirim data
        $.ajax({
            url: 'http://127.0.0.1:8000/api/produk/tambah',
            method: 'POST',
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