@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4" id="product-list">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Product Lists</h6>
      <a href="{{route ('produk.create') }}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add Product"><i class="fas fa-plus"></i> Add Product</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="product-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>No</th>
              <th>ID Produk</th>
              <th>Kategori</th>
              <th>Produk</th>
              <th>Gambar</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="product-table-body">
            <!-- Product data will be populated here -->
          </tbody>
        </table>
      </div>
    </div>
</div>

<div class="card shadow mb-4" id="product-detail" style="display:none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Product Detail</h6>
        <button class="btn btn-secondary btn-sm float-right" id="back-to-list">Back to Product List</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="product-detail-table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Produk</th>
                        <th>Produk</th>
                        <th>Youtube Link</th>
                        <th>Stok</th>
                        <th>Berat</th>
                        <th>HPP</th>
                        <th>Harga</th>
                        <th>Tipe</th>
                        <th>Opsi</th>
                        <th>Gambar Variasi</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="product-detail-body">
                    <!-- Product detail data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@push('scripts')
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
   $(document).ready(function() {
    // Fetch product data from API
    fetchProducts();
    setupNonaktifButtons();

    function fetchProducts() {
        $.ajax({
            url: 'http://127.0.0.1:8000/api/produk',
            method: 'GET',
            success: function(response) {
                populateProductTable(response.data);
            },
            error: function(error) {
                console.error('Error fetching products:', error);
            }
        });
    }

    function setupNonaktifButtons() {
        // Event delegation for product table nonaktif buttons
        $('#product-table-body').on('click', '.nonaktifBtn', function() {
            const productId = $(this).data('id');
            nonaktifProduct(productId);
        });

        // Event delegation for product detail table nonaktif buttons
        $('#product-detail-body').on('click', '.nonaktifBtn', function() {
            const variationId = $(this).data('id');
            nonaktifProductVariation(variationId);
        });
    }

    function nonaktifProduct(productId) {
    Swal.fire({
        title: "Nonaktifkan Produk?",
        text: "Apakah Anda yakin ingin menonaktifkan produk ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Nonaktifkan!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `http://127.0.0.1:8000/api/produk/status/${productId}`,
                method: 'PUT',
                contentType: 'application/json', // Tambahkan content type
                data: JSON.stringify({ 
                    status: 'nonaktif',
                    id_produk: productId 
                }), // Gunakan data bukan body
                success: function(response) {
                    Swal.fire({
                        title: "Dinonaktifkan!",
                        text: "Produk berhasil dinonaktifkan.",
                        icon: "success"
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    // Tampilkan pesan error dari server jika ada
                    const errorMessage = xhr.responseJSON 
                        ? xhr.responseJSON.message 
                        : "Terjadi kesalahan saat menonaktifkan produk.";
                    
                    Swal.fire({
                        title: "Gagal!",
                        text: errorMessage,
                        icon: "error"
                    });
                    
                    // Log error untuk debugging
                    console.error('Error details:', xhr.responseJSON);
                }
            });
        }
    });
}

function nonaktifProductVariation(variationId) {
    Swal.fire({
        title: "Nonaktifkan Variasi Produk?",
        text: "Apakah Anda yakin ingin menonaktifkan variasi produk ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Nonaktifkan!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `http://127.0.0.1:8000/api/produk-variasi/status/${variationId}`,
                method: 'PUT',
                contentType: 'application/json', // Tambahkan content type
                data: JSON.stringify({ 
                    status: 'nonaktif',
                    id_produk_variasi: variationId 
                }), // Gunakan data bukan body
                success: function(response) {
                    Swal.fire({
                        title: "Dinonaktifkan!",
                        text: "Variasi produk berhasil dinonaktifkan.",
                        icon: "success"
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    // Tampilkan pesan error dari server jika ada
                    const errorMessage = xhr.responseJSON 
                        ? xhr.responseJSON.message 
                        : "Terjadi kesalahan saat menonaktifkan variasi produk.";
                    
                    Swal.fire({
                        title: "Gagal!",
                        text: errorMessage,
                        icon: "error"
                    });
                    
                    // Log error untuk debugging
                    console.error('Error details:', xhr.responseJSON);
                }
            });
        }
    });
}

    function getValueOrDefault(value, defaultValue) {
        return value !== null && value !== undefined ? value : defaultValue;
    }

    function populateProductTable(products) {
        const tableBody = $('#product-table-body');
        let rows = '';

        products.forEach((product, index) => {
            const kategoriName = getValueOrDefault(product.kategori?.nama_kategori, '-');
            const productName = getValueOrDefault(product.nama_produk, '-');
            const productImage = getValueOrDefault(product.gambar_produk[ 0]?.gambar, 'default_image_url');
            const productId = getValueOrDefault(product.id_produk, '-');

            rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${productId}</td>
                    <td>${kategoriName}</td>
                    <td>${productName}</td>
                    <td><img src="${productImage}" class="img-fluid" style="max-width:80px" alt="${productName}"></td>
                    <td>
                        <a href="#" class="btn btn-primary btn-sm detailBtn" data-id="${product.id_produk}">Detail</a>
                        <a href="{{url('produk/edit/${product.id_produk}')}}" class="btn btn-primary btn-sm">Edit</a>
                        <button type="button" class="btn btn-danger btn-sm nonaktifBtn" data-id="${product.id_produk}">Nonaktif</button>
                    </td>
                </tr>
            `;
        });

        tableBody.html(rows);
        $('#product-dataTable').DataTable();

        setupNonaktifButtons();
    }

    function populateProductDetailTable(product) {
        const detailBody = $('#product-detail-body');
        let rows = '';

        const youtubeLink = product.detail_produk?.url_video ? 
            `<a href="${product.detail_produk.url_video}" target="_blank">Watch Video</a>` : 'N/A';

        product.produk_variasi.forEach((variation, index) => {
            const id_variasi_produk = variation.id_produk_variasi;
            const type = variation.detail_produk_variasi.map(v => getValueOrDefault(v.opsi_variasi.tipe_variasi.nama_tipe, '-')).join(', ');
            const option = variation.detail_produk_variasi.map(v => getValueOrDefault(v.opsi_variasi.nama_opsi, '-')).join(', ');
            const images = variation.gambar_variasi.map(img => `<img src="${getValueOrDefault(img.gambar, 'default_image_url')}" class="img-fluid" style="max-width:80px" alt="Variation Image">`).join(' ');

            rows += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${getValueOrDefault(product.id_produk, '-')}</td>
                    <td>${getValueOrDefault(product.nama_produk, '-')}</td>
                    <td>${youtubeLink}</td>
                    <td>${getValueOrDefault(variation.stok, '-')}</td>
                    <td>${getValueOrDefault(variation.berat, '-')}</td>
                    <td>${getValueOrDefault(variation.hpp, '-')}</td>
                    <td>${getValueOrDefault(variation.harga, '-')}</td>
                    <td>${type}</td>
                    <td>${option}</td>
                    <td>${images}</td>
                    <td>
                        <a href="{{url('produk/edit/${product.id_produk}')}}" class="btn btn-primary btn-sm">Edit</a>
                        <button type="button" class="btn btn-danger btn-sm nonaktifBtn" data-id="${id_variasi_produk}">Nonaktif</button>
                    </td>
                </tr>
            `;
        });

        detailBody.html(rows);
        $('#product-detail-table').DataTable();

        setupNonaktifButtons();
    }

    // Show product details
    $(document).on('click', '.detailBtn', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        fetchProductDetail(productId);
    });

    function fetchProductDetail(productId) {
        $.ajax({
            url: `http://127.0.0.1:8000/api/produk/${productId}`,
            method: 'GET',
            success: function(response) {
                populateProductDetailTable(response.data);
                $('#product-list').hide();
                $('#product-detail').show();
            },
            error: function(error) {
                console.error('Error fetching product detail:', error);
            }
        });
    }

    // Back to product list
    $('#back-to-list').on('click', function() {
        $('#product-detail').hide();
        $('#product-list').show();
    });
});
  </script>
@endpush