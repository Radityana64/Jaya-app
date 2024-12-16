@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left">Daftar Pelanggan</h6>
        <!-- <a href="#" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Tambah Pelanggan"><i class="fas fa-plus"></i> Tambah Pelanggan</a> -->
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="pelanggan-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Pelanggan</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="pelanggan-table-body">
                    <!-- Data akan diisi oleh JavaScript -->
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
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }

    let table;
    let pelangganData = []; // Untuk menyimpan data pelanggan

    document.addEventListener('DOMContentLoaded', function() {
        // Ambil data dari API dengan JWT token
        fetch('http://127.0.0.1:8000/api/pelanggan/master', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Authorization': `Bearer ${getJwtToken()}`
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            pelangganData = data.pelanggan; // Simpan data pelanggan
            populateTable(pelangganData);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            swal("Error", "Gagal mengambil data pelanggan", "error");
        });

        function populateTable(pelanggan) {
            const tableBody = document.getElementById('pelanggan-table-body');
            let rows = '';

            // Gunakan counter untuk penomoran berurutan
            let counter = 1;

            pelanggan.forEach((item) => {
                rows += `
                    <tr>
                        <td>${counter++}</td>
                        <td>${item.id_pelanggan}</td>
                        <td>${item.username}</td>
                        <td>${item.user ? item.user.nama_lengkap : '-'}</td>
                        <td>${item.user ? item.user.email : '-'}</td>
                        <td>${item.telepon}</td>
                        <td>
                            <a href="#" class="btn btn-primary btn-sm" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-danger btn-sm dltBtn" data-id="${item.id_pelanggan}" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                `;
            });

            tableBody.innerHTML = rows;

            // Hancurkan instance DataTable sebelumnya jika ada
            if ($.fn.DataTable.isDataTable('#pelanggan-dataTable')) {
                $('#pelanggan-dataTable').DataTable().destroy();
            }

            // Inisialisasi DataTable
            table = $('#pelanggan-dataTable').DataTable({
                "columnDefs": [
                    {
                        "orderable": false,
                        "targets": [6] // Kolom aksi
                    }
                ]
            });

            // Pasang event listener untuk tombol hapus
            attachDeleteEvent();
        }

        function attachDeleteEvent() {
            $('.dltBtn').on('click', function() {
                const id = $(this).data('id');
                swal({
                    title: "Apakah Anda yakin?",
                    text: "Setelah dihapus, Anda tidak akan dapat memulihkan data ini!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        // Lakukan aksi hapus
                        fetch(`http://127.0.0.1:8000/api/pelanggan/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Authorization': `Bearer ${getJwtToken()}`
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                swal("Data pelanggan berhasil dihapus!", {
                                    icon: "success",
                                });
                                // Perbarui tabel
                                populateTable(pelangganData.filter(item => item.id_pelanggan !== id));
                            } else {
                                swal("Gagal menghapus pelanggan!");
                            }
                        })
                        .catch(error => console.error('Error menghapus data:', error));
                    }
                });
            });
        }
    });
</script>
@endpush