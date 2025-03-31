@extends('backend.layouts.master')

@section('main-content')

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left">Lis Voucer</h6>
        <a href="{{ route('voucher.create') }}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add Coupon"><i class="fas fa-plus"></i> Tambah Voucer</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="coupon-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Voucer</th>
                        <th>Nama Voucer</th>
                        <th>Diskon</th>
                        <th>Min Pembelian</th>
                        <th>Status</th>
                        <th>Berhasil (Tanggal Mulai - Tanggal Berhenti)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="coupon-table-body">
                    <!-- Data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<style>
    div.dataTables_wrapper div.dataTables_paginate {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

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

    document.addEventListener('DOMContentLoaded', function() {
        const apiUrl = `${getApiBaseUrl()}/api/vouchers`;
        
        // Fetch data from the API
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getJwtToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            populateTable(data.data);
        })
        .catch(error => console.error('Error fetching data:', error));

        function populateTable(coupons) {
            const tableBody = document.getElementById('coupon-table-body');
            let rows = '';

            coupons.forEach((coupon, index) => {
                const tanggalMulai = coupon.tanggal_mulai.split(' ')[0]; // Mengambil hanya tanggal
                const tanggalAkhir = coupon.tanggal_akhir.split(' ')[0];
                rows += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${coupon.kode_voucher}</td>
                        <td>${coupon.nama_voucher}</td>
                        <td>${coupon.diskon}%</td>
                        <td>${coupon.min_pembelian}</td>
                        <td>${coupon.status}</td>
                        <td>${tanggalAkhir} - ${tanggalMulai}</td>
                        <td>
                            <a href="/voucher/edit/${coupon.id_voucher}" class="btn btn-primary btn-sm">Lihat</a>
                            <button class="btn btn-danger btn-sm dltBtn" data-id="${coupon.id_voucher}">Nonaktif</button>
                        </td>
                    </tr>
                `;
            });

            tableBody.innerHTML = rows;

            // Initialize DataTable
            $('#coupon-dataTable').DataTable();

            // Attach delete event listeners
            attachDeleteEvent();
        }

        function attachDeleteEvent() {
            $(document).on('click', '.dltBtn', async function() {
                const idCoupon = $(this).data('id');

                const result = await Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Setelah dinonaktifkan, kupon ini tidak bisa dikembalikan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Ya, nonaktifkan!",
                    cancelButtonText: "Batal"
                });

                if (!result.isConfirmed) return; // Jika batal, hentikan eksekusi

                try {
                    const response = await fetch(`${getApiBaseUrl()}/api/vouchers/nonaktif/${idCoupon}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${getJwtToken()}`,
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            status: 'nonaktif' // Mengirimkan status nonaktif dalam body request
                        })
                    });

                    if (!response.ok) throw new Error("Gagal menonaktifkan kupon.");

                    await Swal.fire({
                        title: "Sukses!",
                        text: "Kupon berhasil dinonaktifkan.",
                        icon: "success"
                    });

                    // Ambil ulang data untuk memperbarui tabel
                    const dataResponse = await fetch(`${getApiBaseUrl()}/api/vouchers`, {
                        method: 'GET',
                        headers: {
                            'Authorization': `Bearer ${getJwtToken()}`,
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await dataResponse.json();
                    populateTable(data.data);
                } catch (error) {
                    console.error("Error:", error);
                    Swal.fire("Gagal!", "Terjadi kesalahan saat menonaktifkan kupon.", "error");
                }
            });
        }
    });
</script>
@endpush