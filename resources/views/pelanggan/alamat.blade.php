<div class="card">
    <div class="card-header">
        <h3>Alamat Saya</h3>
    </div>
    <div class="card-body">
        <div id="address-list">
            <!-- addresses will be dynamically populated here -->
        </div>
        <button class="btn btn-primary mt-3" id="add-address-btn">Tambah Alamat Baru</button>

        <!-- Modal untuk Tambah/Edit Alamat -->
        <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-title-container bg-primary text-white p-3">
                        <h5 class="modal-title mb-0" id="modalTitle">Tambah Alamat Baru</h 5>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        <form id="addressForm">
                            <input type="hidden" id="addressId" name="id_alamat">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="provinsiSelect" class="font-weight-bold">Provinsi</label>
                                        <select class="form-control" id="provinsiSelect" name="provinsi" required>
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kabupatenSelect" class="font-weight-bold">Kabupaten/Kota</label>
                                        <select class="form-control" id="kabupatenSelect" name="kabupaten" required disabled>
                                            <option value="">Pilih Kabupaten</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kodeposSelect" class="font-weight-bold">Kode Pos</label>
                                        <select class="form-control" id="kodeposSelect" name="id_kode_pos" required disabled>
                                            <option value="">Pilih Kode Pos</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="namaJalan" class="font-weight-bold">Nama Jalan</label>
                                        <input type="text" class="form-control" id="namaJalan" name="nama_jalan" placeholder="Masukkan nama jalan" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="detailLokasi" class="font-weight-bold">Detail Lokasi</label>
                                        <input type="text" class="form-control" id="detailLokasi" name="detail_lokasi" placeholder="Contoh: Dekat Minimarket">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" form="addressForm" class="btn btn-primary">Simpan Alamat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fungsi untuk memuat daftar alamat menggunakan AJAX
    function loadAddresses() {
        const jwtToken = getJwtToken();
        $.ajax({
            url: 'http://127.0.0.1:8000/api/alamat',
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                const addressList = $('#address-list');
                addressList.empty(); // Kosongkan daftar sebelumnya

                response.data.forEach(function(address) {
                    const addressCard = `
                        <div class="card mb-2">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5>${address.nama_jalan}</h5>
                                    <p class="mb-1">${address.detail_lokasi || 'Tidak ada detail tambahan'}</p>
                                    <small>${address.kode_pos.nama_kota}, ${address.kode_pos.nama_provinsi} ${address.kode_pos.kode_pos}</small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-primary edit-address mr-2" data-id="${address.id_alamat}">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-address" data-id="${address.id_alamat}">Hapus</button>
                                </div>
                            </div>
                        </div>
                    `;
                    addressList.append(addressCard);
                });

                // Tambahkan event listener untuk tombol edit dan hapus
                attachAddressEventListeners();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Alamat',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat alamat'
                });
            }
        });
    }

    // Fungsi untuk memuat provinsi
    function loadProvinsi() {
        return $.ajax({
            url: 'http://127.0.0.1:8000/api/alamat/provinsi',
            method: 'GET',
            success: function(response) {
                const $provinsiSelect = $('#provinsiSelect');
                $provinsiSelect.empty().append('<option value="">Pilih Provinsi</option>');
                
                const provinsiData = response.data || response;
                provinsiData.forEach(function(prov) {
                    $provinsiSelect.append(`
                        <option value="${prov.id_provinsi}">${prov.provinsi}</option>
                    `);
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Provinsi',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat provinsi'
                });
            }
        });
    }

    // Fungsi untuk memuat kabupaten
    function loadKabupaten(provinsiId) {
        const $kabupatenSelect = $('#kabupatenSelect');
        const $kodeposSelect = $('#kodeposSelect');

        $kabupatenSelect.prop('disabled', true);
        $kodeposSelect.prop('disabled', true);

        return $.ajax({
            url: `http://127.0.0.1:8000/api/alamat/kabupaten/${provinsiId}`,
            method: 'GET',
            success: function(response) {
                $kabupatenSelect.empty().append('<option value="">Pilih Kabupaten</option>');
                
                const kabupatenData = response.data || response;
                kabupatenData.forEach(function(kab) {
                    $kabupatenSelect.append(`
                        <option value="${kab.id_kota}">${kab.nama_kota}</option>
                    `);
                });

                $kabupatenSelect.prop('disabled', false);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Kabupaten',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat kabupaten'
                });
            }
        });
    }

    // Modifikasi fungsi loadKodePos untuk mengembalikan promise
    function loadKodePos(kabupatenId) {
        const $kodeposSelect = $('#kodeposSelect');
        $kodeposSelect.prop('disabled', true);

        return $.ajax({
            url: `http://127.0.0.1:8000/api/alamat/kodepos/${kabupatenId}`,
            method: 'GET',
            success: function(response) {
                $kodeposSelect.empty().append('<option value="">Pilih Kode Pos</option>');
                
                const kodeposData = response.data || response;
                kodeposData.forEach(function(kode) {
                    $kodeposSelect.append(`
                        <option value="${kode.id_kode_pos}">${kode.kode_pos}</option>
                    `);
                });

                $kodeposSelect.prop('disabled', false);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Kode Pos',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat kode pos'
                });
            }
        });
    }

    // Fungsi untuk memuat data alamat berdasarkan id_alamat
    function loadAddressData(addressId) {
        const jwtToken = getJwtToken();
        
        $.ajax({
            url: `http://127.0.0.1:8000/api/alamat/${addressId}`,
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                const address = response.data;
                $('#addressId').val(address.id_alamat);
                $('#namaJalan').val(address.nama_jalan);
                $('#detailLokasi').val(address.detail_lokasi);

                loadProvinsi(address.kode_pos.kota.provinsi.id_provinsi).then(() => {
                $('#provinsiSelect').val(address.kode_pos.kota.provinsi.id_provinsi).change(); // Memilih provinsi yang sesuai
        
                    // Memuat kabupaten sesuai provinsi
                    loadKabupaten(address.kode_pos.kota.provinsi.id_provinsi).then(() => {
                        $('#kabupatenSelect').val(address.kode_pos.id_kota).change(); // Memilih kabupaten yang sesuai
                        
                        // Memuat kode pos sesuai kabupaten
                        loadKodePos(address.kode_pos.id_kota).then(() => {
                            $('#kodeposSelect').val(address.id_kode_pos); // Memilih kode pos yang sesuai
                        });
                    });
                });

                $('#modalTitle').text('Edit Alamat');
                $('#addressModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data Alamat',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat data alamat'
                });
            }
        });
    }


    // Fungsi untuk menambah/edit alamat
    function saveAddress(event) {
        event.preventDefault();
        const jwtToken = getJwtToken();
        const formData = {
            id_kode_pos: $('#kodeposSelect').val(),
            nama_jalan: $('#namaJalan').val(),
            detail_lokasi: $('#detailLokasi').val()
        };
        const addressId = $('#addressId').val();

        $.ajax({
            url: addressId 
                ? `http://127.0.0.1:8000/api/addresses/${addressId}` 
                : 'http://127.0.0.1:8000/api/addresses',
            method: addressId ? 'PUT' : 'POST',
            data: JSON.stringify(formData),
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Alamat berhasil disimpan'
                });

                $('#addressModal').modal('hide');
                loadAddresses(); // Refresh daftar alamat
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan Alamat',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan alamat'
                });
            }
        });
    }
    
    // Fungsi untuk menghapus alamat
    function deleteAddress() {
        const addressId = $(this).data('id');
        const jwtToken = getJwtToken();

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus alamat ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `http://127.0.0.1:8000/api/addresses/${addressId}`,
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${jwtToken}`
                    },
                    success : function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus!',
                            text: 'Alamat berhasil dihapus.'
                        });
                        loadAddresses(); // Refresh daftar alamat
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menghapus Alamat',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus alamat'
                        });
                    }
                });
            }
        });
    }

    // Event listener untuk tombol tambah alamat
    $('#add-address-btn').on('click', function() {
        $('#addressForm')[0].reset();
        $('#addressId').val('');
        $('#modalTitle').text('Tambah Alamat Baru');
        loadProvinsi(); // Load provinsi saat menambah alamat baru
        $('#addressModal').modal('show');
    });

    // Event listener untuk perubahan provinsi
    $('#provinsiSelect').on('change', function() {
        const provinsiId = $(this).val();
        if (provinsiId) {
            loadKabupaten(provinsiId);
        } else {
            $('#kabupatenSelect').empty().append('<option value="">Pilih Kabupaten</option>').prop('disabled', true);
            $('#kodeposSelect').empty().append('<option value="">Pilih Kode Pos</option>').prop('disabled', true);
        }
    });

    // Event listener untuk perubahan kabupaten
    $('#kabupatenSelect').on('change', function() {
        const kabupatenId = $(this).val();
        if (kabupatenId) {
            loadKodePos(kabupatenId);
        } else {
            $('#kodeposSelect').empty().append('<option value="">Pilih Kode Pos</option>').prop('disabled', true);
        }
    });

    // Event listener untuk menyimpan alamat
    $('#addressForm').on('submit', saveAddress);

    // Event listener untuk tombol edit dan hapus
    function attachAddressEventListeners() {
        $('.edit-address').on('click', function() {
            const addressId = $(this).data('id');
            loadAddressData(addressId); // Memuat data alamat untuk diedit
        });

        $('.delete-address').on('click', deleteAddress);
    }

    // Memuat daftar alamat saat halaman dimuat
    loadAddresses();
});

</script>
