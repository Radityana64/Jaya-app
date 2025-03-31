@extends('frontend.layouts.master')

@section('title','E-SHOP || PRODUCT PAGE')

@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route ('index')}}">Beranda<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="">Etalase Produk</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Product Style -->
    <section class="product-area shop-sidebar shop section">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 col-md-4 col-12">
                    <div class="shop-sidebar" style="position: sticky; top: 20px;">
                        <!-- Single Widget -->
                        <div class="single-widget category">
                            <h3 class="title">Kategori</h3>
                            <ul class="categor-list" id="categoryList">
                                <!-- Categories will be loaded here -->
                            </ul>
                        </div>
                        <!--/ End Single Widget -->
                        <!-- Shop By Price -->
                        <div class="single-widget range">
                            <h3 class="title">Berdasarkan Harga</h3>
                            <div class="price-filter">
                                <div class="price-filter-inner">
                                    <div id="slider-range"></div>
                                    <div class="product_filter">
                                        <button type="button" class="filter_button" onclick="filterByPrice()">Filter</button>
                                        <div class="label-input">
                                            <span>Harga Minimum:</span>
                                            <input type="number" id="minPrice" placeholder="Min" />
                                        </div>
                                        <div class="label-input">
                                            <span>Harga Maksimal:</span>
                                            <input type="number" id="maxPrice" placeholder="Max" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ End Shop By Price -->
                    </div>
                </div>
                <!-- Main Content -->
                <div class="col-lg-9 col-md-8 col-12">
                    <div class="row">
                        <div class="col-12">
                            <!-- Shop Top -->
                            <div class="shop-top">
                                <div class="shop-shorter">
                                    <div class="single-shorter">
                                        <button type="button" class="filter_button" onclick="clearFilters()">Semua Produk</button>                                   
                                    </div>
                                    <div class="single-shorter">
                                        <label>Urutkan Berdasarkan :</label>
                                        <select id="sortBy" onchange="sortProducts(this.value)">
                                            <option value="">Default</option>
                                            <option value="name">Nama Produk</option>
                                            <option value="price">Harga Produk</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!--/ End Shop Top -->
                        </div>
                    </div>
                    <div class="row" id="productGrid">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ End Product Style -->
@endsection

@push('styles')
<style>
    select {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .filter_button {
        text-align: center;
        background: #797979;
        padding: 8px 16px;
        margin-top: 10px;
        color: white;
        cursor: pointer;
    }

    .product-img img {
        width: 100%;
        height: 100%; /* Sesuai rasio 3:4 */
        object-fit: cover;
    }

    .product-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 10px;
    }

    .product-content {
        padding: 0 15px 20px 15px; 
        text-align: left; 
    }

    .product-name {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .current-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #797979;
    }

    .shop-sidebar {
        position: sticky;
        top: 20px; /* Jarak dari atas */
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui/1.12.1/jquery-ui.min.js"></script>
<script>
function getApiBaseUrl() {
    return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
}

let allProducts = [];
let categories = [];
let priceRange = { min: 0, max: 1000000 };

// Fetch products from API
async function fetchProducts(searchTerm = '', categoryTerm = '') {
    try {
        const response = await fetch(`${getApiBaseUrl()}/api/produk`);
        const responseData = await response.json();
        
        if (responseData.status === 'success' && Array.isArray(responseData.data)) {
            allProducts = responseData.data.filter(product => product.harga !== null);
            
            // Filter products by search term
            if (searchTerm) {
                allProducts = allProducts.filter(product => {
                    // Cek apakah nama produk mengandung searchTerm
                    const matchesProductName = product.nama_produk.toLowerCase().includes(searchTerm.toLowerCase());

                    // Cek apakah kategori produk mengandung searchTerm
                    const matchesCategory = product.kategori.nama_kategori.toLowerCase().includes(searchTerm.toLowerCase());

                    // Kembalikan true jika salah satu dari kondisi di atas terpenuhi
                    return matchesProductName || matchesCategory;
                });
            }

            if (categoryTerm) {
                allProducts = allProducts.filter(product => 
                    product.kategori.id_kategori === parseInt(categoryTerm) || 
                    product.kategori.id_induk === parseInt(categoryTerm)
                );
            }
            
            
            // Set price range after fetching products
            if (allProducts.length > 0) {
                priceRange.min = Math.min(...allProducts.map(p => p.harga));
                priceRange.max = Math.max(...allProducts.map(p => p.harga));
                initializePriceSlider();
            }
            
            displayProducts(allProducts);
        } else {
            console.error('Invalid data structure received:', responseData);
            displayProducts([]);
        }
    } catch (error) {
        console.error('Error fetching products:', error);
        displayProducts([]);
    }
}

// Fetch categories from API
async function fetchCategories() {
    try {
        const response = await fetch(`${getApiBaseUrl()}/api/kategori`);
        const data = await response.json();
        categories = data.data;
        displayCategories(data.data);
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
}

// Display products in grid
function displayProducts(products) {
    const productGrid = document.getElementById('productGrid');
    productGrid.innerHTML = '';

    if (!Array.isArray(products) || products.length === 0) {
        productGrid.innerHTML = '<div class="col-12"><h3 class="text-center">No products found</h3></div>';
        return;
    }

    products.forEach(product => {
        let imageUrl = 'placeholder.jpg';
        if (product.gambar_produk && product.gambar_produk.length > 0 && product.gambar_produk[0].gambar) {
            imageUrl = product.gambar_produk[0].gambar;
        }

        const productHtml = `
            <div class="col-lg-3 col-md-6 col-12">
                <div class="single-product product-card">
                    <div class="product-img">
                        <a href="/produk-detail/${product.id_produk}" class="product-link">
                            <img class="default-img card-img-top" 
                                src="${imageUrl}" 
                                alt="${product.nama_produk}">
                        </a>
                    </div>
                    <div class="product-content">
                        <h3 class="product-name mb-2">
                            <a href="/produk-detail/${product.id_produk}" class="text-dark" style="text-decoration: none;">
                                ${product.nama_produk}
                            </a>
                        </h3>
                        <div class="product-price-container">
                            <span class="current-price" style="font-size: 1.2rem; font-weight: 700;">
                                Rp ${product.harga.toLocaleString()}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        productGrid.innerHTML += productHtml;
    });
}

// Display categories
function displayCategories(categories) {
    const categoryList = document.getElementById('categoryList');
    categoryList.innerHTML = '<li><a href="#" onclick="filterByCategory(\'all\')">Hapus Filter</a></li>';

    categories.forEach(category => {
        // Buat elemen kategori
        const categoryHtml = `
            <li>
                <a href="#" onclick="filterByCategory(${category.id_kategori})">${category.nama_kategori}</a>
                ${category.sub_kategori && category.sub_kategori.length > 0 ? `
                    <ul class="sub-category" style="padding-left: 20px;">
                        ${category.sub_kategori.map(subCat => 
                            `<li><a href="#" onclick="filterBySubCategory(${subCat.id_kategori})">${subCat.nama_kategori}</a></li>`
                        ).join('')}
                    </ul>
                ` : ''}
            </li>
        `;
        categoryList.innerHTML += categoryHtml;
    });
}

// Initialize price slider
function initializePriceSlider() {
    $("#slider-range").slider({
        range: true,
        min: priceRange.min,
        max: priceRange.max,
        values: [priceRange.min, priceRange.max],
        slide: function(event, ui) {
            // Update the min and max price inputs based on the slider values
            $("#minPrice").val(ui.values[0]);
            $("#maxPrice").val(ui.values[1]);
        }
    });

    // Set initial values for the min and max price inputs
    $("#minPrice").val($("#slider-range").slider("values", 0));
    $("#maxPrice").val($("#slider-range").slider("values", 1));
}

// Filter by price
function filterByPrice() {
    const minPrice = parseInt(document.getElementById('minPrice').value) || priceRange.min;
    const maxPrice = parseInt(document.getElementById('maxPrice').value) || priceRange.max;

    const filtered = allProducts.filter(product => 
        product.harga >= minPrice && product.harga <= maxPrice
    );
    displayProducts(filtered);
}

function filterByCategory(categoryId) {
    if (categoryId === 'all') {
        displayProducts(allProducts);
        return;
    }

    const selectedCategory = categories.find(cat => cat.id_kategori === categoryId);

    if (!selectedCategory) {
        // Jika kategori tidak ditemukan, tampilkan produk kosong
        displayProducts([]);
        return;
    }

    let filtered;

    if (selectedCategory.level === "1") {
        // Jika kategori level 1, ambil semua subkategori
        const subCategoryIds = selectedCategory.sub_kategori.map(subCat => subCat.id_kategori);
        // Tambahkan id kategori level 1 ke dalam array subCategoryIds
        subCategoryIds.push(selectedCategory.id_kategori);
        filtered = allProducts.filter(product => 
            subCategoryIds.includes(product.kategori.id_kategori)
        );
    } else if (selectedCategory.level === "2") {
        // Jika kategori level 2, ambil produk yang sesuai dengan kategori ini
        filtered = allProducts.filter(product => 
            product.kategori.id_kategori === categoryId
        );
    }

    currentPage = 1; // Reset to first page when filtering
    displayProducts(filtered);
}

// Fungsi untuk menangani klik pada subkategori
function filterBySubCategory(subCategoryId) {
    const filtered = allProducts.filter(product => 
        product.kategori.id_kategori === subCategoryId
    );

    currentPage = 1; // Reset to first page when filtering
    displayProducts(filtered);
}

// Sort products
function sortProducts(criteria) {
    let sorted = [...allProducts];
    if (criteria === 'name') {
        sorted.sort((a, b) => a.nama_produk.localeCompare(b.nama_produk));
    } else if (criteria === 'price') {
        sorted.sort((a, b) => a.harga - b.harga);
    }
    currentPage = 1; // Reset to first page when sorting
    displayProducts(sorted);
}

// Update pagination
function updatePagination(totalPages) {
    const pageInfo = document.getElementById('pageInfo');
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');

    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevPage.disabled = currentPage === 1;
    nextPage.disabled = currentPage === totalPages;
}

// Change page function
function changePage(direction) {
    const filteredProducts = allProducts; // You can modify this to use the currently filtered products
    const totalPages = Math.ceil(filteredProducts.length / productsPerPage);

    currentPage += direction;

    if (currentPage < 1) {
        currentPage = 1;
    } else if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    displayProducts(filteredProducts);
}

function clearFilters() {
    window.location.href = '/etalase/produk/'; // Redirect ke halaman produk grid tanpa filter
}


// Initialize
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search') || '';
    const categoryTerm = urlParams.get('category') || '';
    fetchProducts(searchTerm, categoryTerm);
    fetchCategories();
});
</script>
@endpush