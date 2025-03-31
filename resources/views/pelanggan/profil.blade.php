<div class="card">
    <div class="card-header">
        <h3>Profil Saya</h3>
    </div>
    <div class="card-body">
        <form id="profileForm">
            <div class="row">
                <div class="col-md-12">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nama Lengkap</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nama_lengkap" 
                                name="nama_lengkap"
                                placeholder="Nama Lengkap"
                            >
                        </div>
                        <div class="col-md-6">
                            <label>Username</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username"
                                placeholder="Username"
                            >
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Email</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email"
                                placeholder="Email"
                            >
                        </div>
                        <div class="col-md-6">
                            <label>Telepon</label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="telepon" 
                                name="telepon"
                                placeholder="Nomor Telepon"
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button 
                                type="submit" 
                                class="btn btn-primary w-100"
                            >
                                Perbarui Profil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    function getJwtToken() {
        return $('meta[name="api-token"]').attr('content');
    }
    function getApiBaseUrl() {
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }
    function loadProfileData() {
        const jwtToken = getJwtToken();

        $.ajax({
            url: `${getApiBaseUrl()}/api/user/profil`,
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                $('#nama_lengkap').val(response.data.nama_lengkap);
                $('#username').val(response.data.pelanggan.username);
                $('#email').val(response.data.email);
                $('#telepon').val(response.data.pelanggan.telepon);
            },
            error: function(xhr, status, error) {
                console.error('Gagal memuat profil:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Profil',
                    text: 'Tidak dapat mengambil data profil'
                });
            }
        });
    }

    loadProfileData();

    function formatPhoneNumber(phone) {
        phone = phone.replace(/\D/g, ''); // Hapus semua karakter non-digit

        if (phone.startsWith("08")) {
            return "628" + phone.substring(2);
        } else if (phone.startsWith("+628")) {
            return "628" + phone.substring(4);
        } else if (phone.startsWith("628")) {
            return phone;
        }

        return ""; // Jika tidak sesuai format, kosongkan (untuk validasi)
    }

    $('#telepon').on('input', function() {
        let value = $(this).val();
        value = value.replace(/\D/g, ''); // Hanya angka

        if (value.length > 14) {
            value = value.substring(0, 14); // Batasi panjang 14 angka
        }

        $(this).val(value);
    });

    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        let teleponInput = $('#telepon').val();

        // Cek apakah format awal nomor telepon sesuai
        if (!/^(\+628|628|08)\d{7,11}$/.test(teleponInput)) {
            Swal.fire({
                icon: 'error',
                title: 'Nomor Telepon Tidak Valid',
                text: 'Masukan Nomor Dengan Benar.'
            });
            return;
        }

        // Format nomor telepon ke standar 628
        const formattedPhone = formatPhoneNumber(teleponInput);
        if (formattedPhone === "") {
            Swal.fire({
                icon: 'error',
                title: 'Nomor Telepon Tidak Valid',
                text: 'Nomor telepon tidak sesuai format yang diperbolehkan.'
            });
            return;
        }

        const jwtToken = getJwtToken();
        const formData = {
            nama_lengkap: $('#nama_lengkap').val(),
            email: $('#email').val(),
            username: $('#username').val(),
            telepon: formattedPhone
        };

        $.ajax({
            url: `${getApiBaseUrl()}/api/pelanggan/update`,
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profil Berhasil Diperbarui',
                        showConfirmButton: true,
                    });
                    loadProfileData();
                } else {
                    let errorMessage = 'Gagal memperbarui profil';
                    if (response.errors) {
                        // Menggabungkan semua pesan error menjadi satu string dengan pemisah baris
                        errorMessage = Object.keys(response.errors)
                            .map(key => `${key}: ${response.errors[key].join(', ')}`)
                            .join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memperbarui Profil',
                        text: errorMessage,
                        whiteSpace: 'pre-line' // Memastikan baris baru ditampilkan dengan benar
                    });
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Silakan coba lagi nanti';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Menangani error validasi dari response JSON
                    errorMessage = Object.keys(xhr.responseJSON.errors)
                        .map(key => `${key}: ${xhr.responseJSON.errors[key].join(', ')}`)
                        .join('<br>'); // Menggunakan <br> untuk baris baru
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    html: errorMessage // Menggunakan html, bukan text
                });
            }
        });
    });
});
</script>
