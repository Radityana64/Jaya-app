@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4" id="all-categories">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Category Lists</h6>
      <a href="{{route('category.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add User"><i class="fas fa-plus"></i> Add Category</a>
      <button class="btn btn-secondary btn-sm float-right mr-2" id="kategori1-btn">Kategori 1</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
          <table class="table table-bordered" id="category-dataTable" width="100%" cellspacing="0">
              <thead>
                  <tr>
                      <th>No</th>
                      <th>ID Kategori</th>
                      <th>Kategori</th>
                      <th>ID Sub Kategori</th>
                      <th>Sub Kategori</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody id="category-table-body">
                  <!-- Data will be populated here -->
              </tbody>
          </table>
      </div>
    </div>
</div>

<div class="card shadow mb-4" id="kategori1-table" style="display:none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Kategori 1 List</h6>
        <button class="btn btn-secondary btn-sm float-right mr-2" id="all-btn">All</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="kategori1-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Kategori</th>
                        <th>Kategori</th>
                        <th>Gambar</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="kategori1-table-body">
                    <!-- Data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <style>
      div.dataTables_wrapper div.dataTables_paginate{
          display: none;
      }
  </style>
@endpush

@push('scripts')

  <!-- Page level plugins -->
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="{{asset('backend/js/demo/datatables-demo.js')}}"></script>
  <script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }
      let table;
      let categoriesData = []; // Store fetched categories data

      document.addEventListener('DOMContentLoaded', function() {
        // Fetch data from the API
        fetch('http://127.0.0.1:8000/api/kategori')
            .then(response => response.json())
            .then(data => {
                categoriesData = data.data; // Store the data for later use
                populateTable(categoriesData);
            })
            .catch(error => console.error('Error fetching data:', error));

        function populateTable(categories) {
            const tableBody = document.getElementById('category-table-body');
            let rows = '';

            // Use a counter for sequential numbering
            let counter = 1;

            categories.forEach((category) => {
                const idKategori1 = category.id_kategori;
                const namaKategori1 = category.nama_kategori;
                category.sub_kategori.forEach((sub_kategori) => {
                    const idKategori2 = sub_kategori.id_kategori;
                    const namaKategori2 = sub_kategori.nama_kategori;
                    rows += `
                        <tr>
                            <td>${counter++}</td> <!-- Sequential numbering -->
                            <td>${idKategori1}</td>
                            <td>${namaKategori1}</td>
                            <td>${idKategori2}</td>
                            <td>${namaKategori2}</td>
                            <td>
                                <a href="{{url('kategori/edit/${idKategori1}')}}" class="btn btn-primary btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm nonaktifBtn" data-id="${idKategori2}">Nonaktif</button>
                            </td>
                        </tr>
                    `;
                });
            });

            tableBody.innerHTML = rows;

            // Destroy previous DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#category-dataTable')) {
                $('#category-dataTable').DataTable().destroy();
            }

            // Initialize DataTable
            table = $('#category-dataTable').DataTable({
                "columnDefs": [
                    {
                        "orderable": false,
                        "targets": [3, 4, 5]
                    }
                ]
            });

            // Attach event listeners for delete buttons
            attachNonaktifEvent();
        }

        function attachNonaktifEvent() {
            $('.nonaktifBtn').on('click', function() {
                const id = $(this).data('id');
                swal({
                    title: "Are you sure?",
                    text: "Once nonaktif, you will not be able to recover this category!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willNonaktif) => {
                    if (willNonaktif) {
                        // Perform nonaktif action
                        fetch(`http://127.0.0.1:8000/api/kategori/status/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Authorization': `Bearer ${getJwtToken()}`
                            },
                            body: JSON.stringify({ status: 'nonaktif' })
                        })
                        .then(response => {
                            if (response.ok) {
                                swal("Success! The category has been deactivated.", {
                                    icon: "success",
                                    buttons: false,
                                    
                                });
                                location.reload();
                                // Refresh the table
                            } else {
                                swal("Error deactivating the category!");
                            }
                        })
                        .catch(error => console.error('Error deactivating data:', error));
                    }
                });
            });
        }

        $('#kategori1-btn').on('click', function() {
            const kategori1Data = categoriesData.filter(category => category.sub_kategori && category.sub_kategori.length > 0);
            populateKategori1Table(kategori1Data);
            $('#all-categories').hide(); // Hide the main category table
            $('#kategori1-table').show(); // Show Kategori 1 table
        });

        $('#all-btn').on('click', function() {
            populateTable(categoriesData);
            $('#kategori1-table').hide(); // Hide Kategori 1 table
            $('#all-categories').show(); // Show the main category table
        });

        function populateKategori1Table(kategori1Data) {
            const kategori1TableBody = document.getElementById('kategori1-table-body');
            let rows = '';

            kategori1Data.forEach((category, index) => {
                const idKategori1 = category.id_kategori;
                const namaKategori1 = category.nama_kategori;
                const gambar = category.gambar_kategori;
                rows += `
                    <tr>
                        <td>${index + 1}</td> <!-- Sequential numbering -->
                        <td>${idKategori1}</td>
                        <td>${namaKategori1}</td>
                        <td><img src=${gambar} class="img-fluid" style="max-width:80px" alt="Kategori Gambar"></td>
                        <td>
                            <a href="{{url('kategori/edit/${idKategori1}')}}" class="btn btn-primary btn-sm">Edit</a>
                            <button type="button" class="btn btn-danger btn-sm nonaktifBtn" data-id="${idKategori1}">Nonaktif</button>
                        </td>
                    </tr>
                `;
            });

            kategori1TableBody.innerHTML = rows;

            // Destroy previous DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#kategori1-dataTable')) {
                $('#kategori1-dataTable').DataTable().destroy();
            }

            // Initialize DataTable for Kategori 1
            $('#kategori1-dataTable').DataTable();

            attachNonaktifEvent()
        }
    });
</script>
@endpush