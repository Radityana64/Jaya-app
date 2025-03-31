@extends('frontend.layouts.master')

@section('title','E-SHOP || PRODUCT DETAIL')

@section('main-content')
<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="bread-inner">
                    <ul class="bread-list">
                        <li><a href="{{route('index')}}">Beranda<i class="ti-arrow-right"></i></a></li>
                        <li class="active"><a href="">Detail Produk</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Breadcrumbs -->

<!-- Product Detail Container -->
<div class="container mt-3">
    <div class="product-detail-card card" style="border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="row" id="productDetailContainer">
                <!-- Image Gallery -->
                <div class="col-md-5 col-12">
                    <div class="product-gallery">
                        <div class="main-image mb-3">
                            <img id="mainProductImage" src="" alt="Product Image" 
                                 class="img-fluid rounded" 
                                 style="max-height: 500px; width: 100%; object-fit: cover;">
                        </div>
                        <div class="thumbnail-images d-flex" id="thumbnailContainer" style=overflow: hidden;>
                            <!-- Thumbnails will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-5" style="margin-left:20px">
                    <div class="product-info">
                        <h2 id="productName" class="mb-3"></h2>
                        <div class="ratings mb-3">
                            <span id="averageRating" class="text-warning"></span>
                            <span id="totalReviews" class="text-muted"></span>
                        </div>
                        <!-- Description -->
                        <div class="description mb-4">
                            <h4 class="mb-2">Deskripsi</h4>
                            <p id="productDescription"></p>
                        </div>

                        <div class="price mb-4">
                            <h3 id="productPrice" style="color:black"></h3>
                        </div>

                        <!-- Variations -->
                        <div id="variationsContainer" class="mb-4">
                            <!-- Variations will be populated here -->
                        </div>

                        <!-- Quantity -->
                        <div class="quantity mb-4">
                            <label class="form-label">Jumlah:</label>
                            <div class="input-group">
                                <button id="decreaseQty">-</button><input type="number" id="quantity" value="1" min="1" disabled class="form-control"><button id="increaseQty">+</button>
                            </div>
                            <small id="stockInfo" class="text-muted"></small>
                        </div>

                        <!-- Add to Cart Button -->
                        <button id="addToCartBtn" class="btn btn-primary btn-lg mb-4" disabled>
                            Tambah Ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Section -->
    
    <div class="card-video card mt-3 hidden" id="videoCard" style="border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="product-video" id="videoContainer">
                <!-- Video iframe will be added here if available -->
            </div>
        </div>
    </div>

    <div class="card-detail-produk card mt-3" style="border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="col-12">
                <h3 class="mb-3">Detail Produk</h3>
                <div id="detailproduk">
                        
                </div>
            </div>
        </div>
    </div>

    <div class="card-penilaian card mt-3 mb-3" style="border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="col-12">
                <div class="card-summary card mt-3" style="border-radius: 5px;">
                    <div class="card-body p-2">
                        <h3 class="mb-3">Penilaian</h3>
                        
                        <!-- Summary Review and Filter -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <!-- Summary Section -->
                            <div class="review-summary">
                                <div class="d-flex align-items-center text-warning">
                                    <h4 class="me-3" id="averageRatings">0.0</h4>
                                    <div class="stars" id="starSummary"></div>
                                </div>
                                <p class="text-muted mb-0" id="totalsReviews">0 reviews</p>
                            </div>

                            <!-- Filter Section -->
                            <div class="filter-rating">
                                <label for="filterRating" class="form-label mb-1">Filter Bintang:</label>
                                <select id="filterRating" class="form-select" style="width: 150px;">
                                    <option value="">Seluruhnya</option>
                                    <option value="5">5 Bintang</option>
                                    <option value="4">4 Bintang</option>
                                    <option value="3">3 Bintang</option>
                                    <option value="2">2 Bintang</option>
                                    <option value="1">1 Bintang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                <div id="reviewsContainer">
                    <!-- Reviews will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
        .input-group {
        display: flex;
        align-items: center;
        justify-content: center;
        width : 150px;
    }

    .input-group button {
        background-color: black;
        color: white;
        border: 1px solid black;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 16px;
        transition: 0.3s;
    }

    .input-group button:hover {
        background-color: #333; /* Warna hitam lebih terang saat hover */
    }

    .input-group input {
        text-align: center;
        width: 50px;
        border: 1px solid black;
        margin: 0 5px;
    }

    .variation-option {
		margin: 5px;
		padding: 5px 10px;
		border: 1px solid #ddd;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.variation-option.selected {
		background-color: #000000;
		color: white;
		border-color: #000000;
	}

	.variation-option.disabled {
		background-color: #f8f9fa;
		color: #6c757d;
		cursor: not-allowed;
		opacity: 0.5;
	}
    .product-gallery {
        overflow: hidden; /* Pastikan elemen tidak menembus keluar */
        width: 100%; Pastikan lebar sesuai container
    }
    .thumbnail-images {
        display: flex; /* Susun gambar thumbnail secara horizontal */
        gap: 10px; /* Jarak antar gambar */
        flex-wrap: wrap; /* Bungkus thumbnail jika terlalu panjang */
    }

    .thumbnail-img {
        width: 100px; /* Lebar thumbnail */
        height: 100px; /* Tinggi thumbnail */
        object-fit: cover; /* Agar gambar tidak terdistorsi */
        cursor: pointer; /* Ubah kursor menjadi pointer */
        border-radius: 5px; /* Tambahkan sedikit border radius */
        border: 2px solid transparent; /* Default border */
    }

    #videoContainer iframe {
        aspect-ratio: 16 / 9; /* Rasio video YouTube */
        width: 100%;
        height: auto; /* Akan otomatis menyesuaikan */
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .hidden {
        display: none;
    }

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// Global variables

let eventListenersSetUp = false;
let productData = null;
let reviewsData = null;
let selectedVariations = {};
let currentVariation = null;

function getProductIdFromUrl() {
    const pathArray = window.location.pathname.split('/');
    return pathArray[pathArray.length - 1];
}
function getApiBaseUrl() {
    return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
}

async function fetchProductData() {
    try {
        const productId = getProductIdFromUrl();
        const response = await axios.get(`${getApiBaseUrl()}/api/produk/${productId}`);
        productData = response.data.data;
        await fetchReviews();
        initializeProduct();
        
    } catch (error) {
        console.error('Error fetching product data:', error);
    }
}

async function fetchReviews() {
    try {
        const response = await axios.get(`${getApiBaseUrl()}/api/ulasan/get-by-produk/${productData.id_produk}`);
        reviewsData = response.data.data;
        renderReviews();
    } catch (error) {
        console.error('Error fetching reviews:', error);
    }
}

function renderProductBasics() {
    document.getElementById('productName').textContent = productData.nama_produk;
    document.getElementById('productDescription').textContent = productData.deskripsi;
    
    if (reviewsData) {
        const rating = reviewsData.rating_summary.average_rating;
        const reviews = reviewsData.rating_summary.total_reviews;
        document.getElementById('averageRating').textContent = `${rating} ★`;
        document.getElementById('totalReviews').textContent = `(${reviews} reviews)`;
    }
    const defaultVariation = productData.produk_variasi.find(variation => variation.default === "benar");
    
    if (defaultVariation) {
        // Produk memiliki variasi default
        currentVariation = defaultVariation; // Set current variation
        enableQuantityInput(defaultVariation); // Enable quantity input
        updateProductInfo(defaultVariation); // Update product info
    } else {
        // Produk tidak memiliki variasi default
        const priceRange = getPriceRange(productData.produk_variasi);
        document.getElementById('productPrice').textContent = `Rp ${priceRange}`;
        enableQuantityInput(null); // Disable input until a variation is selected
    }
}

function getPriceRange(variations) {
    // Filter out variations that are not active
    const activeVariations = variations.filter(variation => variation.status === 'aktif');
    
    const prices = activeVariations.map(variation => variation.harga);
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);

    if (minPrice === maxPrice) {
        return minPrice.toLocaleString(); // Jika harga sama, tampilkan satu harga
    } else {
        return `${minPrice.toLocaleString()} - ${maxPrice.toLocaleString()}`; // Tampilkan rentang harga
    }
}


function updateVariationImages(variation) {
    const mainImage = document.getElementById('mainProductImage');
    const thumbnailContainer = document.getElementById('thumbnailContainer');
    thumbnailContainer.innerHTML = '';

    if (variation?.gambar_variasi && variation.gambar_variasi.length > 0) {
        mainImage.src = variation.gambar_variasi[0].gambar;
        thumbnailContainer.innerHTML = variation.gambar_variasi.map((img, index) => `
            <img src="${img.gambar}" 
                 alt="Variation thumbnail" 
                 class="thumbnail-img ${index === 0 ? 'active' : ''}"
                 onclick="changeMainImage('${img.gambar}', this)">
        `).join('');
    } else {
        renderImages();
    }
}

function renderVariations() {
    // if (!productData.produk_variasi || productData.produk_variasi.length === 0) {
    //     // If no variations, enable quantity input directly
    //     enableQuantityInput(null);
    //     return;
    // }

    const variationsContainer = document.getElementById('variationsContainer');
    variationsContainer.innerHTML = '';

    const variationTypes = new Map();
    const variationOptions = new Map();

    // Collect all variation types and options
    productData.produk_variasi.forEach(variation => {
        variation.detail_produk_variasi.forEach(detail => {
            const type = detail.opsi_variasi.tipe_variasi;
            const option = detail.opsi_variasi;

            if (!variationTypes.has(type.id_tipe_variasi)) {
                variationTypes.set(type.id_tipe_variasi, type.nama_tipe);
            }

            if (!variationOptions.has(type.id_tipe_variasi)) {
                variationOptions.set(type.id_tipe_variasi, new Map());
            }

            const optionsForType = variationOptions.get(type.id_tipe_variasi);
            if (!optionsForType.has(option.id_opsi_variasi)) {
                optionsForType.set(option.id_opsi_variasi, option);
            }
        });
    });

    // Render variation selectors
    variationTypes.forEach((typeName, typeId) => {
        const typeContainer = document.createElement('div');
        typeContainer.className = 'variation-type mb-3';
        typeContainer.innerHTML = `<h5>${typeName}</h5>`;

        const optionsContainer = document.createElement('div');
        optionsContainer.className = 'd-flex flex-wrap';
        optionsContainer.dataset.typeId = typeId;

        const options = Array.from(variationOptions.get(typeId).values());
        options.forEach(option => {
            const optionBtn = document.createElement('div');
            optionBtn.className = 'variation-option';
            optionBtn.textContent = option.nama_opsi;
            optionBtn.dataset.typeId = typeId;
            optionBtn.dataset.optionId = option.id_opsi_variasi;
            optionBtn.addEventListener('click', () => selectVariation(typeId, option.id_opsi_variasi));
            optionsContainer.appendChild(optionBtn);
        });

        typeContainer.appendChild(optionsContainer);
        variationsContainer.appendChild(typeContainer);
    });
}

function selectVariation(typeId, optionId) {
    const wasSelected = selectedVariations[typeId] === optionId;
    
    if (wasSelected) {
        // Reset this variation type
        delete selectedVariations[typeId];
        resetVariationSelection();
        return;
    }

    selectedVariations[typeId] = optionId;
    
    // Find valid combinations based on current selection
    const validCombinations = findValidCombinations();
    
    // Update UI
    updateVariationUI(typeId, optionId, validCombinations);
    
    // Check if we have a complete valid variation
    const matchingVariation = findMatchingVariation();
    if (matchingVariation) {
        currentVariation = matchingVariation;
        enableQuantityInput(matchingVariation);
    } else {
        disableQuantityInput();
    }

    updateAddToCartButton();

}

function findValidCombinations() {
    const validCombinations = new Set();
    
    productData.produk_variasi.forEach(variation => {
        // Check if this variation matches current selections
        const isValid = variation.detail_produk_variasi.every(detail => {
            const typeId = detail.opsi_variasi.tipe_variasi.id_tipe_variasi;
            // If we haven't selected this type yet, it's valid
            return !selectedVariations[typeId] || 
                   selectedVariations[typeId] === detail.opsi_variasi.id_opsi_variasi;
        });

        if (isValid) {
            // Add all options from this variation to valid combinations
            variation.detail_produk_variasi.forEach(detail => {
                validCombinations.add(
                    `${detail.opsi_variasi.tipe_variasi.id_tipe_variasi}:${detail.opsi_variasi.id_opsi_variasi}`
                );
            });
        }
    });

    return validCombinations;
}

function updateVariationUI(selectedTypeId, selectedOptionId, validCombinations) {
    document.querySelectorAll('.variation-option').forEach(btn => {
        const btnTypeId = parseInt(btn.dataset.typeId);
        const btnOptionId = parseInt(btn.dataset.optionId);
        
        // Handle selected state
        if (btnTypeId === selectedTypeId) {
            btn.classList.toggle('selected', btnOptionId === selectedOptionId);
        }

        // Handle disabled state
        const combinationKey = `${btnTypeId}:${btnOptionId}`;
        const isValid = validCombinations.has(combinationKey);
        
        btn.classList.toggle('disabled', !isValid);
        btn.style.pointerEvents = isValid ? 'auto' : 'none';
        btn.style.opacity = isValid ? '1' : '0.5';
    });

    updateProductInfo(findMatchingVariation());
}

function resetVariationSelection() {
    selectedVariations = {};
    currentVariation = null;
    
    // Reset UI
    document.querySelectorAll('.variation-option').forEach(btn => {
        btn.classList.remove('selected', 'disabled');
        btn.style.pointerEvents = 'auto';
        btn.style.opacity = '1';
    });

    renderImages();
    updateProductInfo(null);
    disableQuantityInput();
}

function updateProductInfo(variation) {
    const priceElement = document.getElementById('productPrice');
    const stockElement = document.getElementById('stockInfo');
    const addToCartBtn = document.getElementById('addToCartBtn');

    if (!variation) {
        priceElement.textContent = 'Sesuaikan Variasi';
        stockElement.textContent = '';
        addToCartBtn.disabled = true;
        return;
    }

    priceElement.textContent = `Rp ${variation.harga.toLocaleString()}`;
    stockElement.textContent = `Stock: ${variation.stok}`;
    addToCartBtn.disabled = false;

    updateVariationImages(variation);
}

function findMatchingVariation() {
    if (!productData.produk_variasi || productData.produk_variasi.length === 0) {
        return null;
    }

    return productData.produk_variasi.find(variation => {
        return variation.detail_produk_variasi.every(detail => {
            const typeId = detail.opsi_variasi.tipe_variasi.id_tipe_variasi;
            const optionId = detail.opsi_variasi.id_opsi_variasi;
            return selectedVariations[typeId] === optionId;
        });
    });
}

function enableQuantityInput(variation) {
    const input = document.getElementById('quantity');
    input.disabled = false;
    
    if (variation) {
        input.max = variation.stok;
        input.value = 1; // Set default value to 1
    } else {
        input.value = 0; // Reset value if no variation
    }
    
    updateAddToCartButton();
}

function disableQuantityInput() {
    const input = document.getElementById('quantity');
    input.value = 1;
    input.disabled = true;
    document.getElementById('addToCartBtn').disabled = true;
}

function updateQuantity(input) {
    const value = parseInt(input.value);
    const max = parseInt(input.max);
    
    if (isNaN(value) || value < 1) {
        input.value = 1;
    } else if (value > max) {
        input.value = max;
    }
    
    updateAddToCartButton();
}

function updateAddToCartButton() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const addToCartBtn = document.getElementById('addToCartBtn');
    
    if (currentVariation) {
        addToCartBtn.disabled = quantity < 1 || quantity > currentVariation.stok;
    } else if (productData.stok) {
        addToCartBtn.disabled = quantity < 1 || quantity > productData.stok;
    } else {
        addToCartBtn.disabled = true; // Jika tidak ada stok
    }
}

// Fungsi untuk mendapatkan token JWT

async function addToCart() {
    const jwtToken = getJwtToken();
    const quantity = parseInt(document.getElementById('quantity').value);

    if (!jwtToken) {
        Swal.fire({
            title: "Peringatan!",
            text: "Anda harus login untuk menambahkan produk ke keranjang.",
            icon: "warning",
            timer: 1000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = '/login';
        });
        return;
    }
    const cartData = {
        id_produk_variasi: currentVariation ? currentVariation.id_produk_variasi : null,
        id_produk: productData.id_produk,
        jumlah: quantity
    };

    try {
        const response = await fetch(`${getApiBaseUrl()}/api/keranjang/tambah`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${jwtToken}`
            },
            body: JSON.stringify(cartData)
        });

        const result = await response.json(); // Parse respons JSON

        if (!response.ok) {
            // Jika respons tidak OK, tangkap pesan error dari API
            let errorMessage = 'Gagal menambahkan ke keranjang.';
            if (result.errors) {
                // Gabungkan semua pesan error menjadi satu string
                errorMessage = Object.values(result.errors).flat().join(' ');
            } else if (result.message) {
                errorMessage = result.message;
            }
            throw new Error(errorMessage); // Lempar error dengan pesan dari API
        }

        // Jika berhasil, tampilkan Swal sukses
        Swal.fire({
            title: "Berhasil!",
            text: "Produk berhasil ditambahkan ke keranjang!",
            icon: "success",
            timer: 1000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = '/keranjang'; 
        });

    } catch (error) {
        console.error('Error adding to cart:', error);
        // Tampilkan pesan error di Swal
        Swal.fire({
            title: "Gagal!",
            text: error.message || "Terjadi kesalahan saat menambahkan produk ke keranjang.",
            icon: "error",
            confirmButtonText: "OK"
        });
    }
}

// Function to render product images
function renderImages() {
    const mainImage = document.getElementById('mainProductImage');
    const thumbnailContainer = document.getElementById('thumbnailContainer');
    const allImages = [];

    // Tambahkan gambar produk
    if (productData.gambar_produk && productData.gambar_produk.length > 0) {
        allImages.push(...productData.gambar_produk);
    }

    // Tambahkan gambar variasi
    productData.produk_variasi.forEach(variation => {
        if (variation.gambar_variasi && variation.gambar_variasi.length > 0) {
            allImages.push(...variation.gambar_variasi);
        }
    });

    // Inisialisasi dengan gambar pertama
    if (allImages.length > 0) {
        mainImage.src = allImages[0].gambar;

        // Render thumbnail
        thumbnailContainer.innerHTML = allImages.map((img, index) => `
            <img src="${img.gambar}" 
                 alt="Product thumbnail" 
                 class="thumbnail-img ${index === 0 ? 'active' : ''}"
                 onclick="changeMainImage('${img.gambar}', this)">
        `).join('');
    }
}

// Setup video jika tersedia
function videoProduk() {
    const videoCard = document.getElementById('videoCard');
    const videoContainer = document.getElementById('videoContainer');
    
    if (productData.detail_produk?.url_video) {
        const videoId = getYouTubeVideoId(productData.detail_produk.url_video);
        if (videoId) {
            videoCard.classList.remove('hidden');
            videoContainer.innerHTML = `
                <h4 class="mb-3">Video Produk</h4>
                <iframe 
                    src="https://www.youtube.com/embed/${videoId}?autoplay=0&modestbranding=1&rel=0" 
                    title="Video Produk"
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    style="width: 100%; height: 500px; border: none; border-radius: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);"
                ></iframe>
            `;
        } else {
            videoCard.classList.add('hidden');
        }
    } else {
        videoCard.classList.add('hidden');
    }
}

// Function to change the main image when a thumbnail is clicked
function changeMainImage(imageSrc, thumbnail) {
    const mainImage = document.getElementById('mainProductImage');
    mainImage.src = imageSrc;

    // Update active thumbnail class
    document.querySelectorAll('.thumbnail-img').forEach(img => img.classList.remove('active'));
    thumbnail.classList.add('active');
}
	
function getYouTubeVideoId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}


// Render reviews
function renderReviews(filterRating = '') {
    const container = document.getElementById('reviewsContainer');
    
    // Cek apakah reviewsData ada
    if (!reviewsData || !reviewsData.ringkasan_ulasan) {
        container.innerHTML = '<p>No reviews available</p>';
        return;
    }

    const reviews = reviewsData.ringkasan_ulasan;

    // Filter reviews based on selected rating
    const filteredReviews = filterRating
        ? reviews.filter(review => review.rating === parseInt(filterRating))
        : reviews;

    // Handle empty reviews
    if (!filteredReviews.length) {
        container.innerHTML = '<p>No reviews available for this rating</p>';
        return;
    }

    // Render reviews
    container.innerHTML = filteredReviews.map(review => `
        <div class="review-item border-bottom py-3">
            <div class="d-flex justify-content-between">
                <div>
                    <h5>${review.nama_pelanggan}</h5>
                    <div class="rating text-warning">
                        ${generateStarRating(review.rating)}
                    </div>
                </div>
                <small class="text-muted">${formatDate(review.tanggal_dibuat)}</small>
            </div>
            <p class="mb-1">${review.ulasan}</p>
            <small class="text-muted">Variation: ${review.variasi}</small>
            ${review.balasan ? `
                <div class="seller-response mt-2 ps-3 border-start">
                    <small class="text-muted">Seller's Response:</small>
                    <p class="mb-0">${review.balasan.balasan}</p>
                </div>
            ` : ''}
        </div>
    `).join('');
}

function updateReviewSummary() {
    // Tambahkan pengecekan yang lebih detail
    if (!reviewsData || !reviewsData.rating_summary) {
        console.warn('Review summary data is missing');
        return;
    }

    const summary = reviewsData.rating_summary;
    const averageRating = summary.average_rating ? summary.average_rating.toFixed(1) : '0.0';
    const totalsReviews = summary.total_reviews || 0;

    // Debug logging
    console.log('Average Rating:', averageRating);
    console.log('Total Reviews:', totalReviews);

    document.getElementById('averageRatings').innerText = averageRating;
    document.getElementById('totalsReviews').innerText = `${totalsReviews} reviews`;
    
    document.getElementById('starSummary').innerHTML = generateStarRating(averageRating);
}

function generateStarRating(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5 ? 1 : 0;
    const emptyStars = 5 - fullStars - halfStar;
    
    let starHTML = '';
    
    // Bintang penuh
    for (let i = 0; i < fullStars; i++) {
        starHTML += '★';
    }
    
    // Bintang kosong
    for (let i = 0; i < emptyStars; i++) {
        starHTML += '☆';
    }
    
    return starHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

function setupReviewEventListeners() {
    const filterRatingSelect = document.getElementById('filterRating');
    
    if (filterRatingSelect) {
        filterRatingSelect.addEventListener('change', function () {
            renderReviews(this.value);
        });
    }
}

function setupEventListeners() {
    // Only set up event listeners if they haven't been set up already
    if (eventListenersSetUp) return;

    const input = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');

    // Disable buttons if no variation is selected
    function updateQuantityButtons() {
        if (!currentVariation) {
            decreaseBtn.disabled = true;
            increaseBtn.disabled = true;
            input.value = 1; // Reset quantity to 1
            return;
        }

        const maxQuantity = currentVariation.stok; // Get stock for the selected variation
        input.setAttribute('data-max', maxQuantity);
        decreaseBtn.disabled = input.value <= 1;
        increaseBtn.disabled = input.value >= maxQuantity;
    }

    // Decrease quantity
    decreaseBtn.addEventListener('click', () => {
        const currentQty = parseInt(input.value);
        if (currentQty > 1) {
            input.value = currentQty - 1;
            updateQuantityButtons();
        }
    });

    // Increase quantity
    increaseBtn.addEventListener('click', () => {
        if (currentVariation) {
            const currentQty = parseInt(input.value);
            const maxQuantity = currentVariation.stok;
            if (currentQty < maxQuantity) {
                input.value = currentQty + 1;
                updateQuantityButtons();
            }
        }
    });

    // Add to cart functionality
    document.getElementById('addToCartBtn').addEventListener('click', addToCart);

     // Mark that event listeners have been set up
     eventListenersSetUp = true;
}

// Inisialisasi event listeners saat halaman dimuat
// document.addEventListener('DOMContentLoaded', setupEventListeners);

 // Add description detail
function ProductDetails(){
    const detailProdukElement = document.getElementById('detailproduk');

    if(productData.detail_produk){
        const {deskripsi_detail}= productData.detail_produk;

        // Mengisi elemen dengan konten deskripsi detail
        detailProdukElement.innerHTML = `
            <p>${deskripsi_detail}</p>
        `;
    }
}
// Add this to your initializeProduct function
function initializeProduct() {
    renderProductBasics();
    renderVariations();
    renderImages();
    videoProduk();
    ProductDetails();
    setupEventListeners();
    
    if (reviewsData) {
        updateReviewSummary();
        renderReviews();
        setupReviewEventListeners();
    }

}

document.addEventListener('DOMContentLoaded', () => {
    fetchProductData();
    // setupEventListeners();
});

</script>
@endpush