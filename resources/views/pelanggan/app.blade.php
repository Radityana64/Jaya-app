@extends('frontend.layouts.master')

@section('title','E-SHOP || PRODUCT PAGE')

@section('main-content')
<div class="container profile-container py-4" style="margin: 20px;">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="list-group">
                <p data-page="profil" class="list-group-item page-link {{ $activePage ?? 'active' }}">
                    Profil Saya
                </p>
                <p data-page="alamat" class="list-group-item page-link">
                    Alamat
                </p>
                <p data-page="voucher" class="list-group-item page-link">
                    Voucher
                </p>
                <p data-page="pesanan" class="list-group-item page-link">
                    Pesanan
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
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });

    // Handle browser back/forward
    $(window).on('popstate', function() {
        const path = window.location.pathname;
        const page = path.split('/').pop();
        
        // Reload content or update view
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
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });
});
</script>
@endpush