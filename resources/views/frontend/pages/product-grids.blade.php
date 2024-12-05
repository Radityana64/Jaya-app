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
                            <li><a href="index1.html">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="blog-single.html">Shop Grid</a></li>
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
                <div class="col-lg-3 col-md-4 col-12">
                    <div class="shop-sidebar">
                        <!-- Single Widget -->
                        <div class="single-widget category">
                            <h3 class="title">Categories</h3>
                            <ul class="categor-list" id="categoryList">
                                <!-- Categories will be loaded here -->
                            </ul>
                        </div>
                        <!--/ End Single Widget -->
                        <!-- Shop By Price -->
                        <div class="single-widget range">
                            <h3 class="title">Shop by Price</h3>
                            <div class="price-filter">
                                <div class="price-filter-inner">
                                    <div id="slider-range"></div>
                                    <div class="product_filter">
                                        <button type="button" class="filter_button" onclick="filterByPrice()">Filter</button>
                                        <div class="label-input">
                                            <span>Min Price:</span>
                                            <input type="number" id="minPrice" placeholder="Min" />
                                        </div>
                                        <div class="label-input">
                                            <span>Max Price:</span>
                                            <input type="number" id="maxPrice" placeholder="Max" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ End Shop By Price -->
                    </div>
                </div>
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
                                        <label>Sort By :</label>
                                        <select id="sortBy" onchange="sortProducts(this.value)">
                                            <option value="">Default</option>
                                            <option value="name">Name</option>
                                            <option value="price">Price</option>
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
                    <div class="pagination" id="pagination">
                        <button onclick="changePage(-1)" id="prevPage" disabled>Prev</button>
                        <span id="pageInfo"></span>
                        <button onclick="changePage(1)" id="nextPage">Next</button>
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
        background: #F7941D;
        padding: 8px 16px;
        margin-top: 10px;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .product-img img {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }
    .pagination {
        margin-top: 20px;
        text-align: center;
    }
    .pagination button {
        padding: 10px 15px;
        margin: 0 5px;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui/1.12.1/jquery-ui.min.js"></script>
<script>
let allProducts = [];
let categories = [];
let priceRange = { min: 0, max: 1000000 };
let currentPage = 1;
const productsPerPage = 15;

// Fetch products from API
async function fetchProducts(searchTerm = '', categorySearchTerm = '') {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/produk');
        const responseData = await response.json();
        
        if (responseData.status === 'success' && Array.isArray(responseData.data)) {
            allProducts = responseData.data.filter(product => product.harga !== null);
            
            // Filter products by search term
            if (searchTerm) {
                allProducts = allProducts.filter(product => 
                    product.nama_produk.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            // Filter products by category search term
            if (categorySearchTerm) {
                const categoryIds = categories
                    .filter(category => 
                        category.nama_kategori.toLowerCase().includes(categorySearchTerm.toLowerCase())
                    )
                    .flatMap(category => category.kategori2.map(subCat => subCat.id_kategori_2));
                
                allProducts = allProducts.filter(product => 
                    categoryIds.includes(product.kategori_2.id_kategori_2)
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
        const response = await fetch('http://127.0.0.1:8000/api/kategori');
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

    // Calculate total pages
    const totalPages = Math.ceil(products.length / productsPerPage);
    const startIndex = (currentPage - 1) * productsPerPage;
    const endIndex = Math.min(startIndex + productsPerPage, products.length);
    
    // Display products for the current page
    for (let i = startIndex; i < endIndex; i++) {
        const product = products[i];
        let imageUrl = 'placeholder.jpg';
        if (product.gambar_produk && product.gambar_produk.length > 0 && product.gambar_produk[0].gambar) {
            imageUrl = product.gambar_produk[0].gambar;
        }

        const productHtml = `
            <div class="col-lg-4 col-md-6 col-12">
                <div class="single-product">
                    <div class="product-img">
                        <a href="produk-detail/${product.id_produk}">
                            <img class="default-img" src="${imageUrl}" alt="${product.nama_produk}">
                            <img class="hover-img" src="${imageUrl}" alt="${product.nama_produk}">
                        </a>
                        <div class="button-head">
                            <div class="product-action-2">
                                ${product.deskripsi}
                            </div>
                        </div>
                    </div>
                    <div class="product-content">
                        <h3><a href="product-detail/${product.id_produk}">${product.nama_produk}</a></h3>
                        <div class="product-price">
                            <span>Rp ${product.harga.toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        productGrid.innerHTML += productHtml;
    }

    // Update pagination
    updatePagination(totalPages);
}

// Display categories
function displayCategories(categories) {
    const categoryList = document.getElementById('categoryList');
    categoryList.innerHTML = '<li><a href="#" onclick="filterByCategory(\'all\')">Hapus Filter</a></li>';

    categories.forEach(category => {
        const categoryHtml = `
            <li>
                <a href="#" onclick="filterByCategory(${category.id_kategori_1})">${category.nama_kategori}</a>
                <ul class="sub-category" style="padding-left: 20px;">
                    ${category.kategori2.map(subCat => 
                        `<li><a href="#" onclick="filterBySubCategory(${subCat.id_kategori_2})">${subCat.nama_kategori}</a></li>`
                    ).join('')}
                </ul>
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

    currentPage = 1; // Reset to first page when filtering
    displayProducts(filtered);
}

// Filter by category
function filterByCategory(categoryId) {
    if (categoryId === 'all') {
        displayProducts(allProducts);
        return;
    }
    
    const filtered = allProducts.filter(product => {
        const category = categories.find(cat => 
            cat.kategori2.some(subCat => subCat.id_kategori_2 === product.kategori_2.id_kategori_2)
        );
        return category && category.id_kategori_1 === categoryId;
    });
    currentPage = 1; // Reset to first page when filtering
    displayProducts(filtered);
}

// Filter by subcategory
function filterBySubCategory(subCategoryId) {
    const filtered = allProducts.filter(product => 
        product.kategori_2.id_kategori_2 === subCategoryId
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
    window.location.href = '/'; // Redirect ke halaman produk grid tanpa filter
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search') || '';
    const categorySearchTerm = urlParams.get('categorySearch') || '';
    fetchProducts(searchTerm, categorySearchTerm);
    fetchCategories();
});
</script>
@endpush