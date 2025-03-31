@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left">Daftar Admin</h6>
        <a href="{{route ('admin.create') }}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Tambah Admin"><i class="fas fa-plus"></i> Tambah Admin</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="admin-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID User</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="admin-table-body">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
<style>
    div.dataTables_wrapper div.dataTables_paginate{
        display: none;
    }
</style>
@endpush

@push('scripts')
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }
    
    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }
    let table;
    let adminData = []; // Untuk menyimpan data admin

    document.addEventListener('DOMContentLoaded', function() {
        // Ambil data dari API dengan JWT token
        fetch(`${getApiBaseUrl()}/api/data/admin`, {
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
            populateTable(data.data);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            Swal.fire("Error", "Gagal mengambil data admin", "error");
        });

        function populateTable(admin) {
            const tableBody = document.getElementById('admin-table-body');
            let rows = '';

            // Gunakan counter untuk penomoran berurutan
            let counter = 1;

            admin.forEach((item) => {
                rows += `
                    <tr>
                        <td>${counter++}</td>
                        <td>${item.id_user}</td>
                        <td>${item.nama_lengkap}</td>
                        <td>${item.email}</td>
                        <td>
                            <button class="btn btn-danger btn-sm hapusBtn" data-id="${item.id_user}" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tableBody.innerHTML = rows;

            // Hancurkan instance DataTable sebelumnya jika ada
            if ($.fn.DataTable.isDataTable('#admin-dataTable')) {
                $('#admin-dataTable').DataTable().destroy();
            }

            // Inisialisasi DataTable
            table = $('#admin-dataTable').DataTable({
                "columnDefs": [
                    {
                        "orderable": false,
                        "targets": [4] // Kolom aksi
                    }
                ]
            });

            // Pasang event listener untuk tombol hapus
            attachNonaktifEvent();
        }

        function attachNonaktifEvent() {
            document.querySelectorAll('.hapusBtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: "Apakah Anda yakin ingin menghapus admin ini?",
                        text: "Tindakan ini tidak dapat dikembalikan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`${getApiBaseUrl()}/api/data/admin/delete/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Authorization': `Bearer ${getJwtToken()}`
                                }
                            })
                            .then(response => {
                                if (response.ok) {
                                    Swal.fire({
                                        title: "Dihapus!",
                                        text: "Admin berhasil dinonaktifkan.",
                                        icon: "success"
                                    }).then(() => location.reload()); // Reload halaman setelah sukses
                                } else {
                                    Swal.fire({
                                        title: "Gagal!",
                                        text: "Terjadi kesalahan saat menghapus admin.",
                                        icon: "error"
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error menghapus admin:', error);
                                Swal.fire({
                                    title: "Error!",
                                    text: "Terjadi kesalahan pada server.",
                                    icon: "error"
                                });
                            });
                        }
                    });
                });
            });
        }

    });
</script>
@endpush