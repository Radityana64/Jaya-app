<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PemesananController;
use App\Http\Controllers\Api\PengirimanController;
use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AlamatDetailController;
use App\Http\Controllers\Api\UlasanController;
Use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\BannerController;

use App\Http\Controllers\Api\GambarController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth
Route::post('pelanggan/register', [AuthController::class, 'register']);
Route::post('user/login', [AuthController::class, 'login']);
Route::get('pelanggan/master', [AuthController::class, 'getMasterPelanggan'])->middleware('auth.jwt', 'jwt.role:admin,pemilik_toko');

Route::middleware(['auth.jwt'])->group(function(){
    Route::get('user/profil', [AuthController::class, 'getUser']);
    Route::put('pelanggan/update', [AuthController::class, 'updateProfil']);
    Route::post('logout', [AuthController::class, 'logout']);
});

//Lupa Password
Route::prefix('password')->group(function () {
    Route::post('forgot', [ResetPasswordController::class, 'forgotPassword']);
    Route::get('validate/{token}', [ResetPasswordController::class, 'validateToken']);
    Route::put('reset/{resetToken}', [ResetPasswordController::class, 'resetPassword']);
});
//Alamat Raja Ongkir
Route::get('/alamat/ambil-data', [AlamatController::class, 'ambilData']);
Route::get('/alamat/provinsi', [AlamatController::class, 'ambilProvinsi']);
Route::get('/alamat/kabupaten/{provinsiId}', [AlamatController::class, 'getKabupaten']);
Route::get('/alamat/kodepos/{kabupatenId}', [AlamatController::class, 'getKodePos']);

//Alamat Pelanggan
Route::middleware(['auth.jwt', 'jwt.role:pelanggan'])->group(function(){
    Route::post('/addresses', [AlamatDetailController::class, 'store']);
    Route::put('/addresses/{id_alamat}', [AlamatDetailController::class, 'update']);
    Route::delete('/addresses/{id_alamat}', [AlamatDetailController::class, 'destroy']);
    Route::get('/alamat', [AlamatDetailController::class, 'getAlamatByPelanggan']);
    Route::get('/alamat/{id_alamat}', [AlamatDetailController::class,'getAlamatByIdAlamat']);
});
Route::get('/alamat/data/{id_pelanggan}', [AlamatDetailController::class, 'getAlamatByIdPelanggan'])->middleware('auth.jwt', 'jwt.role:admin,pemilik_toko');

//Produk
Route::get('/kategori', [KategoriController::class, 'getKategori']);
Route::get('/kategori/{id}', [KategoriController::class, 'getKategoriById']);

Route::get('produk/', [ProdukController::class, 'index']);
Route::get('produk/{id}', [ProdukController::class, 'show']);
Route::get('/variasi-produk', [ProdukController::class, 'showVariation']);    

//Produk
Route::middleware(['auth.jwt', 'jwt.role:admin'])->group(function(){
    Route::post('/kategori/create', [KategoriController::class, 'createKategori']);
    Route::put('/kategori/update/{id}', [KategoriController::class, 'updateKategori']);
    Route::put('/kategori/status/{id_kategori}', [KategoriController::class, 'updateStatus']);

    Route::post('produk/tambah/', [ProdukController::class, 'store']);
    Route::put('produk/edit/{id}', [ProdukController::class, 'update']);
    Route::delete('produk/{id}', [ProdukController::class, 'destroy']);
    Route::put('/produk/status/{id}', [ProdukController::class, 'updateStatus']);
    Route::put('/produk-variasi/status/{variationId}', [ProdukController::class, 'updateVariationStatus']);
});

//Voucher
Route::middleware(['auth.jwt'])->group(function(){
    Route::prefix('vouchers')->group(function () {
        Route::post('/', [VoucherController::class, 'store'])->middleware('jwt.role:admin');
        Route::put('/{id}', [VoucherController::class, 'update'])->middleware('jwt.role:admin');
        Route::put('/nonaktif/{id}', [VoucherController::class, 'nonaktif'])->middleware('jwt.role:admin');
        Route::post('/distribusi', [VoucherController::class, 'distribusiVoucher'])->middleware('jwt.role:admin');
        Route::post('/gunakan', [VoucherController::class, 'gunakanVoucher'])->middleware('jwt.role:pelanggan');
        Route::get('/active', [VoucherController::class, 'getActiveVouchersForCustomer'])->middleware('jwt.role:pelanggan');
        Route::get('/pelanggan/{id_voucher}', [VoucherController::class, 'getVoucherById'])->middleware('jwt.role:admin');
        Route::get('/', [VoucherController::class, 'getAllVouchers'])->middleware('jwt.role:admin');
        // Route::get('/active/all', [VoucherController::class, 'getAllActiveVouchers']);
    });
});

//Pemesanan
Route::middleware(['auth.jwt', 'jwt.role:pelanggan'])->group(function(){
    //Keranjang
    Route::get('/keranjang', [PemesananController::class, 'GetKeranjang']);
    Route::post('/keranjang/tambah', [PemesananController::class, 'TambahKeKeranjang']);
    Route::put('/keranjang/update/{IdDetail}', [PemesananController::class, 'UpdateItemKeranjang']);
    Route::delete('/keranjang/delete/{IdDetail}', [PemesananController::class, 'DeleteItemKeranjang']);
    //Ongkir
    Route::post('/pilih-alamat-pengiriman', [PengirimanController::class, 'pilihAlamatPengiriman']);
    Route::get('/opsi-pengiriman', [PengirimanController::class, 'getOpsiPengiriman']);
    Route::post('/pilih-jasa/{id_pemesanan}', [PengirimanController::class, 'pilihJasaPengiriman']);
    //Payment Gateway
    Route::get('/pemesanan/data', [PemesananController::class, 'getPemesanan']);
    Route::post('/payments/create-payment', [PembayaranController::class, 'createPayment']);
    Route::post('/payments/snap', [PembayaranController::class, 'storeSnapToken']);
   //Status Pengiriman
    Route::put('pengiriman/diterima/{id_pengiriman}', [PengirimanController::class, 'updateStatusDiterima']);
    //Ulasan Pelanggan
    Route::post('ulasan/buat', [UlasanController::class, 'storeUlasan']);
});

//PaymentGateway
Route::post('payments/callback', [PembayaranController::class, 'callback']);
//Ulasan
Route::get('ulasan/get-by-produk/{id_produk}', [UlasanController::class, 'getUlasanProduk']);

Route::middleware(['auth.jwt', 'jwt.role:admin'])->group(function(){
    Route::put('pengiriman/dikirim/{id_pengiriman}', [PengirimanController::class, 'updateStatusDikirim']);
    Route::get('/pemesanan/data/master', [PemesananController::class, 'getPemesananMaster']);
    Route::post('ulasan/balasan/{id_ulasan}', [UlasanController::class, 'SimpanBalasan']);

    //Laporan 
    Route::post('/laporan/penjualan', [LaporanController::class, 'getLaporanPenjualan']);

    //Banner
    Route::post('/banner/create', [BannerController::class, 'create']);
    Route::put('/banners/{id}', [BannerController::class, 'update']);
    Route::patch('/banners/nonaktif/{id}', [BannerController::class, 'deactivate']);
    Route::get('/banners/aktif/{id}', [BannerController::class, 'getActiveBannersById']);
});
Route::get('/banners/aktif', [BannerController::class, 'getActiveBanners']);

//Ekstensi + Coba Coba
Route::post('upload-gambar', [GambarController::class, 'uploadGambar']);
