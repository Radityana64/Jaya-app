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
                      <th>Id Kategori 1</th>
                      <th>Kategori 1</th>
                      <th>Id Kategori 2</th>
                      <th>Kategori 2</th>
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
                        <th>Id Kategori 1</th>
                        <th>Kategori 1</th>
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

            categories.forEach((category1) => {
                const idKategori1 = category1.id_kategori_1;
                const namaKategori1 = category1.nama_kategori;
                category1.kategori2.forEach((category2) => {
                    const idKategori2 = category2.id_kategori_2;
                    const namaKategori2 = category2.nama_kategori;
                    rows += `
                        <tr>
                            <td>${counter++}</td> <!-- Sequential numbering -->
                            <td>${idKategori1}</td>
                            <td>${namaKategori1}</td>
                            <td>${idKategori2}</td>
                            <td>${namaKategori2}</td>
                            <td>
                                <a href="{{url('category/${idKategori1}/edit')}}" class="btn btn-primary btn-sm">Edit</a>
                                <form method="POST" action="{{url('category/${idKategori1}')}}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm dltBtn" data-id="${idKategori1}">Delete</button>
                                </form>
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
            attachDeleteEvent();
        }

        function attachDeleteEvent() {
            $('.dltBtn').on('click', function() {
                const id = $(this).data('id');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this imaginary file!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        // Perform delete action
                        fetch(`http://127.0.0.1:8000/api/kategori/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                swal("Poof! Your imaginary file has been deleted!", {
                                    icon: "success",
                                });
                                // Refresh the table
                                populateTable(categoriesData);
                            } else {
                                swal("Error deleting the category!");
                            }
                        })
                        .catch(error => console.error('Error deleting data:', error));
                    }
                });
            });
        }

        $('#kategori1-btn').on('click', function() {
            const kategori1Data = categoriesData.filter(category1 => category1.kategori2 && category1.kategori2.length > 0);
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

            kategori1Data.forEach((category1, index) => {
                const idKategori1 = category1.id_kategori_1;
                const namaKategori1 = category1.nama_kategori;
                rows += `
                    <tr>
                        <td>${index + 1}</td> <!-- Sequential numbering -->
                        <td>${idKategori1}</td>
                        <td>${namaKategori1}</td>
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
        }
      });
  </script>
@endpush