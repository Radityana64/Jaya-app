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
                        <th>Status</th>
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
    let pelangganData = []; // Untuk menyimpan data pelanggan

    document.addEventListener('DOMContentLoaded', function() {
        // Ambil data dari API dengan JWT token
        fetch(`${getApiBaseUrl()}/api/pelanggan/master`, {
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
            Swal.fire({
                title: "Error!",
                text: `Gagal mengambil data pelanggan: ${error.message || "Terjadi kesalahan"}`,
                icon: "error",
                confirmButtonText: "OK"
            });
        });


        function populateTable(pelanggan) {
            const tableBody = document.getElementById('pelanggan-table-body');
            let rows = '';

            // Gunakan counter untuk penomoran berurutan
            let counter = 1;

            pelanggan.forEach((item) => {
                // Tentukan tombol berdasarkan status
                const statusButton = item.status === 'aktif' 
                    ? `<button class="btn btn-danger btn-sm nonaktifBtn" data-id="${item.id_pelanggan}" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Nonaktif">
                        <i class="fas fa-ban"></i>
                    </button>`
                    : `<button class="btn btn-success btn-sm aktifBtn" data-id="${item.id_pelanggan}" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Aktifkan">
                        <i class="fas fa-check"></i>
                    </button>`;

                rows += `
                    <tr>
                        <td>${counter++}</td>
                        <td>${item.id_pelanggan}</td>
                        <td>${item.username}</td>
                        <td>${item.user ? item.user.nama_lengkap : '-'}</td>
                        <td>${item.user ? item.user.email : '-'}</td>
                        <td>${item.telepon}</td>
                        <td>${item.status}</td>
                        <td>
                            <a href="/pelanggan/detail-pesanan/${item.id_pelanggan}" class="btn btn-primary btn-sm" data-id="${item.id_pelanggan}" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Detail">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            ${statusButton}
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
                        "targets": [7] // Kolom aksi
                    }
                ]
            });

            // Pasang event listener untuk tombol status
            attachStatusEvent();
        }

        function attachStatusEvent() {
            // Event untuk tombol Nonaktif
            document.querySelectorAll('.nonaktifBtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');

                    if (!id) {
                        Swal.fire({
                            title: "Kesalahan!",
                            text: "ID pelanggan tidak ditemukan.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Apakah Anda yakin?",
                        text: "Pelanggan akan dinonaktifkan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, Nonaktifkan!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`${getApiBaseUrl()}/api/pelanggan/nonaktif/${id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Authorization': `Bearer ${getJwtToken()}`
                                }
                            })
                            .then(response => {
                                if (response.ok) {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Pelanggan berhasil dinonaktifkan.",
                                        icon: "success",
                                        confirmButtonText: "OK"
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        title: "Gagal!",
                                        text: "Gagal menonaktifkan pelanggan.",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                }
                            })
                            .catch(error => {
                                console.error("Error menonaktifkan pelanggan:", error);
                                Swal.fire({
                                    title: "Kesalahan!",
                                    text: "Terjadi kesalahan saat menghubungi server.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            });
                        }
                    });
                });
            });

            // Event untuk tombol Aktif
            document.querySelectorAll('.aktifBtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');

                    if (!id) {
                        Swal.fire({
                            title: "Kesalahan!",
                            text: "ID pelanggan tidak ditemukan.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Apakah Anda yakin?",
                        text: "Pelanggan akan diaktifkan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745", // Warna hijau untuk aktif
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, Aktifkan!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`${getApiBaseUrl()}/api/pelanggan/aktif/${id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Authorization': `Bearer ${getJwtToken()}`
                                }
                            })
                            .then(response => {
                                if (response.ok) {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Pelanggan berhasil diaktifkan.",
                                        icon: "success",
                                        confirmButtonText: "OK"
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        title: "Gagal!",
                                        text: "Gagal mengaktifkan pelanggan.",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                }
                            })
                            .catch(error => {
                                console.error("Error mengaktifkan pelanggan:", error);
                                Swal.fire({
                                    title: "Kesalahan!",
                                    text: "Terjadi kesalahan saat menghubungi server.",
                                    icon: "error",
                                    confirmButtonText: "OK"
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