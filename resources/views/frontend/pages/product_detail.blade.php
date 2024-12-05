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
								<li><a href="#">Home<i class="ti-arrow-right"></i></a></li>
								<li class="active"><a href="">Shop Details</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Breadcrumbs -->

<!-- Product Detail Container -->
<div class="container mt-3">
    <div class="row" id="productDetailContainer">
        <!-- Image Gallery -->
        <div class="col-md-6 col-12">
            <div class="product-gallery">
                <div class="main-image mb-3">
                    <img id="mainProductImage" src="" alt="Product Image" class="img-fluid rounded">
                </div>
                <div class="thumbnail-images d-flex" id="thumbnailContainer">
                    <!-- Thumbnails will be populated here -->
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-6">
            <div class="product-info">
                <h2 id="productName" class="mb-3"></h2>
                <div class="ratings mb-3">
                    <span id="averageRating" class="text-warning"></span>
                    <span id="totalReviews" class="text-muted"></span>
                </div>
				<!-- Description -->
                <div class="description">
                    <h4>Description</h4>
                    <p id="productDescription"></p>
                </div>

                <div class="price mb-3">
                    <h3 id="productPrice"></h3>
                </div>

                <!-- Variations -->
                <div id="variationsContainer" class="mb-4">
                    <!-- Variations will be populated here -->
                </div>

                <!-- Quantity -->
                <div class="quantity mb-4">
                    <label class="form-label">Quantity:</label>
                    <div class="input-group" style="width: 100px">
                        
                    <button id="decreaseQty">-</button><input type="number" id="quantity" value="1" min="1" disabled class="form-control"><button id="increaseQty">+</button>
                       

                    </div>
                    <small id="stockInfo" class="text-muted"></small>
                </div>

                <!-- Add to Cart Button -->
                <button id="addToCartBtn" class="btn btn-primary btn-lg mb-4" disabled>
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Video Section -->
    <div class="product-video mt-5" id="videoContainer">
        <!-- Video iframe will be added here if available -->
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h3>Produk Detail</h3>
            <div id="detailproduk">
                
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h3>Customer Reviews</h3>
            <div id="reviewsContainer">
                <!-- Reviews will be populated here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .variation-option {
		margin: 5px;
		padding: 5px 10px;
		border: 1px solid #ddd;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.variation-option.selected {
		background-color: #007bff;
		color: white;
		border-color: #007bff;
	}

	.variation-option.disabled {
		background-color: #f8f9fa;
		color: #6c757d;
		cursor: not-allowed;
		opacity: 0.5;
	}
    .thumbnail-images img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        margin-right: 10px;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .thumbnail-images img.active {
        border-color: #007bff;
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

async function fetchProductData() {
    try {
        const productId = getProductIdFromUrl();
        const response = await axios.get(`/api/produk/${productId}`);
        productData = response.data.data;
        await fetchReviews();
        initializeProduct();
        
    } catch (error) {
        console.error('Error fetching product data:', error);
    }
}

async function fetchReviews() {
    try {
        const response = await axios.get(`/api/ulasan/get-by-produk/${productData.id_produk}`);
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
    if (!productData.produk_variasi || productData.produk_variasi.length === 0) {
        // If no variations, enable quantity input directly
        enableQuantityInput(null);
        return;
    }

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
        priceElement.textContent = 'Select all variations';
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
    } else if (productData.stok) {
        input.max = productData.stok;
        input.value = 1; // Set default value to 1
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
        alert('Anda harus login untuk menambahkan produk ke keranjang.');
        window.location.href = '/login';
        return;
    }

    const cartData = {
        id_produk_variasi: currentVariation ? currentVariation.id_produk_variasi : null,
        id_produk: productData.id_produk,
        jumlah: quantity
    };

    try {
        const response = await fetch('http://127.0.0.1:8000/api/keranjang/tambah', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${jwtToken}`
            },
            body: JSON.stringify(cartData)
        });

        if (!response.ok) {
            throw new Error('Gagal menambahkan ke keranjang.');
        }

        const result = await response.json();
        alert('Produk berhasil ditambahkan ke keranjang!');
        window.location.href = '/keranjang';

    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Terjadi kesalahan saat menambahkan produk ke keranjang.');
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
    const videoContainer = document.getElementById('videoContainer');
    if (productData.detail_produk?.url_video) {
        const videoId = getYouTubeVideoId(productData.detail_produk.url_video);
        videoContainer.innerHTML = `
            <h4 class="mb-3">Product Video</h4>
            <div class="ratio ratio-16x9">
                <iframe src="https://www.youtube.com/embed/${videoId}" 
                        title="Product Video"
                        allowfullscreen>
                </iframe>
            </div>
        `;
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
function renderReviews() {
    const container = document.getElementById('reviewsContainer');
    if (!reviewsData || !reviewsData.reviews.length) {
        container.innerHTML = '<p>No reviews yet</p>';
        return;
    }

    container.innerHTML = reviewsData.reviews.map(review => `
        <div class="review-item border-bottom py-3">
            <div class="d-flex justify-content-between">
                <h5>${review.nama_pelanggan}</h5>
                <small class="text-muted">${review.tanggal_dibuat}</small>
            </div>
            <div class="rating">
                ${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}
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
}
// Initialize the page
// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    fetchProductData();
    setupEventListeners();
});

</script>
@endpush