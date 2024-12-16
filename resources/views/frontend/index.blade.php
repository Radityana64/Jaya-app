@extends('frontend.layouts.master')
@section('title','E-SHOP || HOME PAGE')
@section('main-content')

<!-- Slider Area -->
<section id="Gslider" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators" id="carousel-indicators"></ol>
    <div class="carousel-inner" role="listbox" id="carousel-inner"></div>
    <a class="carousel-control-prev" href="#Gslider" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#Gslider" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</section>
<!--/ End Slider Area -->

<!-- Start Small Banner  -->
<section class="small-banner section">
    <div class="container-fluid">
        <div class="row" id="categoryList"></div>
    </div>
</section>
<!-- End Small Banner -->

<!-- Start Product Area -->
<div class="product-area section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2>Item</h2>
                </div>
            </div>
        </div>
        <div class="row" id="productList"></div>
    </div>
</div>
<!-- End Product Area -->
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
@endpush

@push('scripts')
<!-- Add these in your layout or head section -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    const apiBanners = 'http://127.0.0.1:8000/api/banners/aktif';
    const apiCategories = 'http://127.0.0.1:8000/api/kategori';
    const apiProducts = 'http://127.0.0.1:8000/api/produk';

    // Fetch and display banners
    async function fetchBanners() {
        try {
            const response = await fetch(apiBanners);
            const result = await response.json();
            const indicators = document.getElementById('carousel-indicators');
            const inner = document.getElementById('carousel-inner');

            // Clear previous content
            indicators.innerHTML = '';
            inner.innerHTML = '';

            if (result.data && result.data.length > 0) {
                result.data.forEach((banner, index) => {
                    // Create indicators
                    const indicator = document.createElement('li');
                    indicator.setAttribute('data-target', '#Gslider');
                    indicator.setAttribute('data-slide-to', index);
                    if (index === 0) indicator.classList.add('active');
                    indicators.appendChild(indicator);

                    // Create carousel items
                    const item = document.createElement('div');
                    item.classList.add('carousel-item');
                    if (index === 0) item.classList.add('active');
                    item.innerHTML = `
                        <img class="first-slide" src="${banner.gambar_banner}" alt="${banner.judul}" style="width: 100%; height: 550px; object-fit: cover;">
                        <div class="carousel-caption d-none d-md-block text-left">
                            <h1>${banner.judul}</h1>
                            <p>${banner.deskripsi}</p>
                            <a class="btn btn-lg ws-btn" href="{{route('produk.grids')}}" role="button">Shop Now<i class="far fa-arrow-alt-circle-right"></i></a>
                        </div>
                    `;
                    inner.appendChild(item);
                });

                // Initialize carousel
                $('#Gslider').carousel();
            } else {
                console.log('No banners found');
                inner.innerHTML = `
                    <div class="carousel-item active">
                        <img class="first-slide" src="https://via.placeholder.com/1920x550" alt="No Banner" style="width: 100%; height: 550px; object-fit: cover;">
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error fetching banners:', error);
        }
    }

    // Fetch and display categories
    async function fetchCategories() {
    try {
        const response = await fetch(apiCategories);
        const result = await response.json();
        const categoryList = document.getElementById('categoryList');

        // Clear previous content
        categoryList.innerHTML = '';

        if (result.data && result.data.length > 0) {
            const categoriesContainer = document.createElement('div');
            categoriesContainer.classList.add('category-carousel', 'owl-carousel');

            result.data.filter(category => category.level === '1').forEach(category => {
                const categoryItem = document.createElement('div');
                categoryItem.classList.add('single-banner');
                categoryItem.innerHTML = `
                    <div style="height: 300px; display: flex; flex-direction: column;">
                        <img src="${category.gambar_kategori || 'https://via.placeholder.com/400x300'}" 
                             alt="${category.nama_kategori}" 
                             style="width: 100%; height: 300px; object-fit: cover; max-width: 540px;">
                        <div class="content" style="padding: 10px; text-align: center;">
                            <h3>${category.nama_kategori}</h3>
                            <a href="/etalase/produk?category=${category.id_kategori}">Discover Now</a>
                        </div>
                    </div>
                `;
                categoriesContainer.appendChild(categoryItem);
            });

            categoryList.appendChild(categoriesContainer);

            // Initialize Owl Carousel for categories
            $(document).ready(function() {
                $('.category-carousel').owlCarousel({
                    loop: true,
                    margin: 2,
                    nav: true,
                    responsive: {
                        0: { items: 1 },
                        600: { items: 3 },
                        1000: { items: 3 }
                    }
                });
            });
        } else {
            console.log('No categories found');
            categoryList.innerHTML = '<p>No categories available</p>';
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
}
    // Fetch and display products
    async function fetchProducts() {
        try {
            const response = await fetch(apiProducts);
            const result = await response.json();
            const productList = document.getElementById('productList');

            // Clear previous content
            productList.innerHTML = '';

            if (result.data && result.data.length > 0) {
                // Limit to 6 products
                const limitedProducts = result.data.slice(0, 6);

                limitedProducts.forEach(product => {
                    const productHtml = `
                        <div class="col-sm-6 col-md-4 col-lg-4 p-b-35">
                            <div class="single-product">
                                <div class="product-img">
                                    <a href="/produk-detail/${product.id_produk}">
                                        <img class="default-img" src="${product.gambar_produk[0]?.gambar || 'https://via.placeholder.com/300x300'}" 
                                             alt="${product.nama_produk}" 
                                             style="height: 300px; object-fit: cover;">
                                        ${product.harga === null ? '' : `<span class="new">New</span>`}
                                    </a>
                                </div>
                                <div class="product-content">
                                    <h3><a href="/produk-detail/${product.id_produk}">${product.nama_produk}</a></h3>
                                    <div class="product-price">
                                        <span>Rp ${product.harga ? product.harga.toLocaleString() : 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    productList.innerHTML += productHtml;
                });
            } else {
                console.log('No products found');
                productList.innerHTML = '<p>No products available</p>';
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    // Initialize the page
    async function init() {
        await fetchBanners();
        await fetchCategories();
        await fetchProducts();
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', init);
</script>
@endpush