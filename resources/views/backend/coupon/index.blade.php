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
        <h6 class="m-0 font-weight-bold text-primary float-left">Coupon List</h6>
        <a href="{{ route('voucher.create') }}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Add Coupon"><i class="fas fa-plus"></i> Add Coupon</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="coupon-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Voucher</th>
                        <th>Nama Voucher</th>
                        <th>Diskon</th>
                        <th>Min Pembelian</th>
                        <th>Status</th>
                        <th>Berhasil (Tanggal Mulai - Tanggal Berhenti)</th>
                        <th>Action</th>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<style>
    div.dataTables_wrapper div.dataTables_paginate {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const apiUrl = 'http://127.0.0.1:8000/api/vouchers';
        
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
                rows += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${coupon.kode_voucher}</td>
                        <td>${coupon.nama_voucher}</td>
                        <td>${coupon.diskon}%</td>
                        <td>${coupon.min_pembelian}</td>
                        <td>${coupon.status}</td>
                        <td>${coupon.tanggal_mulai} - ${coupon.tanggal_akhir}</td>
                        <td>
                            <a href="/coupon/${coupon.id_voucher}/edit" class="btn btn-primary btn-sm">Edit</a>
                            <button class="btn btn-danger btn-sm dltBtn" data-id="${coupon.id_voucher}">Delete</button>
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
            $('.dltBtn').on('click', function() {
                const id = $(this).data('id');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this data!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        fetch(`http://127.0.0.1:8000/api/vouchers/nonaktif/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Authorization': 'Bearer ' + getJwtToken(),
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                swal("Poof! Your coupon has been nonaktif!", {
                                    icon: "success",
                                });
                                // Refresh the table
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
                                });
                            } else {
                                swal("Error for Nonaktif coupon!");
                            }
                        })
                        .catch(error => console.error('Error Nonaktif data:', error));
                    }
                });
            });
        }
    });
</script>
@endpush