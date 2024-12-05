<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Gambar dari Cloudinary</title>
</head>
<body>
    <h1>Gambar dari Cloudinary</h1>
    <img src="https://res.cloudinary.com/dw3ttld0i/image/upload/v1731414461/produk_variasi_images/spcxalvp5usu8p1w7uaa" alt="Gambar dari Cloudinary">

    <!-- <p>Gantilah <code>YOUR_CLOUD_NAME</code> dan <code>YOUR_IMAGE_PUBLIC_ID</code> sesuai dengan informasi gambar Anda.</p> -->
    <div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Daftar Provinsi</h2>
            <div class="form-group">
                <label for="provinsiSelect">Pilih Provinsi</label>
                <select class="form-control" id="provinsiSelect">
                    <option value="">Pilih Provinsi</option>
                </select>
            </div>

            <div class="form-group">
                <label for="kabupatenSelect">Pilih Kabupaten</label>
                <select class="form-control" id="kabupatenSelect" disabled>
                    <option value="">Pilih Kabupaten</option>
                </select>
            </div>

            <div class="form-group">
                <label for="kodeposSelect">Pilih Kode Pos</label>
                <select class="form-control" id="kodeposSelect" disabled>
                    <option value="">Pilih Kode Pos</option>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinsiSelect = document.getElementById('provinsiSelect');
    const kabupatenSelect = document.getElementById('kabupatenSelect');
    const kodeposSelect = document.getElementById('kodeposSelect');

    // Fungsi untuk memuat provinsi
    async function loadProvinsi() {
        try {
            const response = await fetch('/api/alamat/provinsi');
            const data = await response.json();

            console.log('Raw Provinsi Data:', data);

            // Bersihkan pilihan sebelumnya
            provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';

            // Pastikan data adalah array
            const provinsiArray = Array.isArray(data) ? data : (data.data || []);

            provinsiArray.forEach(provinsi => {
                const option = document.createElement('option');
                option.value = provinsi.id_provinsi;
                option.textContent = provinsi.provinsi;
                provinsiSelect.appendChild(option);
            });

            console.log('Provinsi Populated:', provinsiSelect.options.length);
        } catch (error) {
            console.error('Gagal memuat provinsi:', error);
            alert('Tidak dapat memuat data provinsi');
        }
    }

    // Fungsi untuk memuat kabupaten
    async function loadKabupaten(provinsiId) {
        kabupatenSelect.disabled = true;
        kabupatenSelect.innerHTML = '<option value="">Memuat Kabupaten...</option>';
        kodeposSelect.innerHTML = '<option value="">Pilih Kode Pos</option>';
        kodeposSelect.disabled = true;

        try {
            const response = await fetch(`/api/alamat/kabupaten/${provinsiId}`);
            const data = await response.json();

            console.log('Raw Kabupaten Data:', data);

            kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten</option>';

            // Pastikan data adalah array
            const kabupatenArray = Array.isArray(data) ? data : (data.data || []);

            kabupatenArray.forEach(kabupaten => {
                const option = document.createElement('option');
                option.value = kabupaten.id_kota;
                option.textContent = kabupaten.nama_kota;
                kabupatenSelect.appendChild(option);
            });

            kabupatenSelect.disabled = false;
            console.log('Kabupaten Populated:', kabupatenSelect.options.length);
        } catch (error) {
            console.error('Gagal memuat kabupaten:', error);
            kabupatenSelect.innerHTML = '<option value="">Gagal memuat</option>';
        }
    }

    // Fungsi untuk memuat kode pos
    async function loadKodePos(kabupatenId) {
        kodeposSelect.disabled = true;
        kodeposSelect.innerHTML = '<option value="">Memuat Kode Pos...</option>';

        try {
            const response = await fetch(`/api/alamat/kodepos/${kabupatenId}`);
            const data = await response.json();

            console.log('Raw Kode Pos Data:', data);

            kodeposSelect.innerHTML = '<option value="">Pilih Kode Pos</option>';

            // Pastikan data adalah array
            const kodeposArray = Array.isArray(data) ? data : (data.data || []);

            kodeposArray.forEach(kodepos => {
                const option = document.createElement('option');
                option.value = kodepos.id_kode_pos;
                option.textContent = kodepos.kode_pos;
                kodeposSelect.appendChild(option);
            });

            kodeposSelect.disabled = false;
            console.log('Kode Pos Populated:', kodeposSelect.options.length);
        } catch (error) {
            console.error('Gagal memuat kode pos:', error);
            kodeposSelect.innerHTML = '<option value="">Gagal memuat</option>';
        }
    }

    // Event listener untuk provinsi
    provinsiSelect.addEventListener('change', function() {
        const provinsiId = this.value;
        if (provinsiId) {
            loadKabupaten(provinsiId);
        }
    });

    // Event listener untuk kabupaten
    kabupatenSelect.addEventListener('change', function() {
        const kabupatenId = this.value;
        if (kabupatenId) {
            loadKodePos(kabupatenId);
        }
    });

    // Muat provinsi saat halaman dimuat
    loadProvinsi();
});
</script>

</body>
</html>
