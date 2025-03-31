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
<section class="kategori-banner section">
    <div class="container">
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
                    <h2>Produk</h2>
                </div>
            </div>
        </div>
        <div class="row" id="productList"></div>
    </div>
</div>
<!-- End Product Area -->
@endsection

@push('scripts')
<script>
    function getApiBaseUrl() {
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }
    const apiBanners = `${getApiBaseUrl()}/api/banners/aktif`;
    const apiCategories = `${getApiBaseUrl()}/api/kategori`;
    const apiProducts = `${getApiBaseUrl()}/api/produk`;

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
                            <p style="color: white;">${banner.deskripsi}</p>
                            <a class="btn btn-lg ws-btn" href="{{route('produk.grids')}}" role="button">Belanja Sekarang<i class="far fa-arrow-alt-circle-right"></i></a>
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
                        <div class="category-card" style="width: 90%; margin: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); border-radius:10px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;">
                            <div class="row no-gutters align-items-center">
                                <div class="col-5">
                                    <img src="${category.gambar_kategori || 'https://via.placeholder.com/100x100'}" 
                                        alt="${category.nama_kategori}" 
                                        style="width: 100%; height: 100px; object-fit: cover;">
                                </div>
                                <div class="col-7">
                                    <div class="px-2 py-2">
                                        <h6 class="mb-1" style="font-size: 0.9rem;">${category.nama_kategori}</h6>
                                        <a href="/etalase/produk?category=${category.id_kategori}" class="text-primar" style="font-size: 0.8rem; text-decoration: none;">
                                            Tampilkan <i class="fa fa-chevron-right" style="font-size: 0.7rem; color: #797979"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    categoriesContainer.appendChild(categoryItem);
                });

                categoryList.appendChild(categoriesContainer);

                // Initialize Owl Carousel for categories (keeping the original carousel logic)
                $(document).ready(function() {
                    $('.category-carousel').owlCarousel({
                        loop: true,
                        margin: 2,
                        nav: true,
                        responsive: {
                            0: { items: 1 },
                            600: { items: 4 },
                            1000: { items: 4 }
                        }
                    });
                });
                
                // Add hover effect to all category cards
                document.querySelectorAll('.category-card').forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.transform = 'scale(1.03)';
                        card.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'scale(1)';
                        card.style.boxShadow = '0 2px 5px rgba(0,0,0,0.08)';
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
                // Limit to 8 products
                const limitedProducts = result.data.slice(0, 8);

                limitedProducts.forEach(product => {
                    const productHtml = `
                        <div class="col-6 col-md-3 mb-3">
                            <div class="product-card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.08); height: 380px; transition: transform 0.3s, box-shadow 0.3s;">
                                <a href="/produk-detail/${product.id_produk}" style="text-decoration: none; color: inherit;">
                                    <div class="product-img">
                                        <img src="${product.gambar_produk[0]?.gambar || 'https://via.placeholder.com/300x400'}" 
                                            alt="${product.nama_produk}" 
                                            style="width: 100%; height: 300px; object-fit: cover;">
                                    </div>
                                    <div class="product-content p-1 d-flex flex-column justify-content-center align-items-center" style="height: 70px;">
                                        <h3 class="product-name mb-1" style="font-size: 0.9rem; font-weight: normal; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: center;"> 
                                            ${product.nama_produk}
                                        </h3>
                                        <div class="product-price" style="font-size: 1rem; font-weight: bold;">
                                            Rp ${product.harga ? product.harga.toLocaleString() : 'N/A'}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    `;
                    productList.innerHTML += productHtml;
                });

                // Add hover effect to all product cards
                document.querySelectorAll('.product-card').forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.transform = 'scale(1.03)';
                        card.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'scale(1)';
                        card.style.boxShadow = '0 2px 5px rgba(0,0,0,0.08)';
                    });
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