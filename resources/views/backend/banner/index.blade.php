@extends('backend.layouts.master')

@section('main-content')
<!-- DataTales Example -->
<div class="card shadow mb-4" id="banner-list">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left">Banner Lists</h6>
        <a href="{{ route('banner.create') }}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add Banner"><i class="fas fa-plus"></i> Add Banner</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="banner-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="banner-table-body">
                    <!-- Banner data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Fetch banner data from API
    fetchBanners();

    function fetchBanners() {
        $.ajax({
            url: 'http://127.0.0.1:8000/api/banners/aktif', // Ganti dengan endpoint API yang sesuai
            method: 'GET',
            success: function(response) {
                populateBannerTable(response.data);
            },
            error: function(error) {
                console.error('Error fetching banners:', error);
            }
        });
    }

    function populateBannerTable(banners) {
        const tableBody = $('#banner-table-body');
        let rows = '';

        banners.forEach((banner, index) => {
            const bannerId = banner.id_banner;
            const bannerTitle = banner.judul;
            const bannerImage = banner.gambar_banner || 'default_image_url'; // Ganti dengan URL default jika tidak ada gambar

            rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${bannerId}</td>
                    <td>${bannerTitle}</td>
                    <td><img src="${bannerImage}" class="img-fluid" style="max-width:80px" alt="${bannerTitle}"></td>
                    <td>
                        <a href="{{ url('banner/edit/${bannerId}') }}" class="btn btn-primary btn-sm">Edit</a>
                        <button type="button" class="btn btn-danger btn-sm nonaktifBtn" data-id="${bannerId}">Nonaktif</button>
                    </td>
                </tr>
            `;
        });

        tableBody.html(rows);
        $('#banner-dataTable').DataTable();

        setupNonaktifButtons();
    }

    function setupNonaktifButtons() {
        $('#banner-table-body').on('click', '.nonaktifBtn', function() {
            const bannerId = $(this).data('id');
            nonaktifBanner(bannerId);
        });
    }

    function nonaktifBanner(bannerId) {
        Swal.fire({
            title: "Nonaktifkan Banner?",
            text: "Apakah Anda yakin ingin menonaktifkan banner ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Nonaktifkan!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `http://127.0.0.1:8000/api/banners/nonaktif/${bannerId}`, // Ganti dengan endpoint API yang sesuai
                    method: 'PATCH',
                    success: function(response) {
                        Swal.fire({
                            title: "Dinonaktifkan!",
                            text: "Banner berhasil dinonaktifkan.",
                            icon: "success"
                        }).then(() => {
                            fetchBanners(); // Refresh the banner list
                        });
                    },
                    error: function(xhr, status, error) {
                        const errorMessage = xhr.responseJSON 
                            ? xhr.responseJSON.message 
                            : "Terjadi kesalahan saat menonaktifkan banner.";
                        
                        Swal.fire({
                            title: "Gagal!",
                            text: errorMessage,
                            icon: "error"
                        });
                        
                        console.error('Error details:', xhr.responseJSON);
                    }
                });
            }
        });
    }
});
</script>
@endpush