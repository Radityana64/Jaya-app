<div class="card">
    <div class="card-header">
        <h3>Profil Saya</h3>
    </div>
    <div class="card-body">
        <form id="profileForm">
            <div class="row">
                <div class="col-md-8">
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
    // Fungsi untuk mengambil token JWT dari meta tag
    function getJwtToken() {
        return $('meta[name="api-token"]').attr('content');
    }

    // Fungsi untuk memuat data profil
    function loadProfileData() {
        const jwtToken = getJwtToken();

        $.ajax({
            url: 'http://127.0.0.1:8000/api/pelanggan/profil',
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${jwtToken}`,
                'Accept': 'application/json'
            },
            success: function(response) {
                // Isi form dengan data profil
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

    // Panggil fungsi load profil saat halaman dimuat
    loadProfileData();

    // Handler untuk submit form
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        const jwtToken = getJwtToken();
        const formData = {
            nama_lengkap: $('#nama_lengkap').val(),
            email: $('#email').val(),
            username: $('#username').val(),
            telepon: $('#telepon').val()
        };

        $.ajax({
            url: 'http://127.0.0.1:8000/api/pelanggan/update',
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

                    // Muat ulang data profil setelah update berhasil
                    loadProfileData();
                } else {
                    let errorMessage = 'Gagal memperbarui profil';
                    if (response.errors) {
                        errorMessage = Object.values(response.errors).flat().join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memperbarui Profil',
                        text: errorMessage
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Silakan coba lagi nanti'
                });
            }
        });
    });
});
</script>