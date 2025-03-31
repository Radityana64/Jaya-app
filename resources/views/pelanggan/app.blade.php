@extends('frontend.layouts.master')

@section('title','E-SHOP || PRODUCT PAGE')

@section('main-content')
<div class="container">
    <div class="profile-container py-4" style="margin-top: 20px;">
        <div class="row">
            <div class="col-md-3 sidebar">
                <div class="list-group">
                    <p data-page="profil" class="list-group-item page-link {{ $activePage ?? 'active' }}">
                        <i class="fa fa-user mr-2"></i>
                        <span class="font-weight-bold">Profil Saya</span>
                    </p>
                    <p data-page="alamat" class="list-group-item page-link">
                        <i class="fa fa-map-marker mr-2"></i>
                        <span class="font-weight-bold">Alamat</span>
                    </p>
                    <p data-page="voucher" class="list-group-item page-link">
                        <i class="fa fa-ticket mr-2"></i>
                        <span class="font-weight-bold">Voucher</span>
                    </p>
                    <p data-page="pesanan" class="list-group-item page-link">
                        <i class="fa fa-shopping-bag mr-2"></i>
                        <span class="font-weight-bold">Pesanan</span>
                    </p>
                </div>
            </div>

            <div class="col-md-9" id="page-content">
                @if(isset($activePage))
                    @include("pelanggan.{$activePage}")
                @else
                    @include('pelanggan.profil')
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.list-group {
    position: sticky;
    top: 90px; /* Sesuaikan dengan tinggi navbar */
    background: white;
    z-index: 900;
    border-bottom: 1px solid #ddd;
    display: flex;
}
.list-group-item {
    border: none;
    border-radius: 8px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    color: #495057;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    background-color: #e9ecef;
    cursor: pointer;
    color: #000000;
}

.list-group-item.active {
    background-color: #000000;
    color: white;
}

.list-group-item.active:hover {
    background-color: #797979;
}

.font-weight-bold {
    font-weight: 600 !important;
}
</style>
@endpush

@push('scripts')
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
$(document).ready(function() {
    const pageLinks = $('.page-link');
    const pageContent = $('#page-content');

    pageLinks.on('click', function(e) {
        e.preventDefault();
        const page = $(this).data('page');

        // Update active state
        pageLinks.removeClass('active');
        $(this).addClass('active');

        // Update URL
        history.pushState(null, '', `/data-pelanggan/${page}`);

        // Load new content via AJAX
        $.ajax({
            url: `/data-pelanggan/${page}`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(html) {
                pageContent.html(html);
            },
            error: function(xhr, status, error) {
                console.error('Gagal memuat halaman:', error);
                Swal.fire({
                    title: "Terjadi Kesalahan!",
                    text: `Gagal memuat halaman: ${xhr.responseText || error}`,
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    });
});
</script>
@endpush