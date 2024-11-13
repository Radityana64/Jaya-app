<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
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
    Route::get('pelanggan/profil', [AuthController::class, 'getPelanggan']);
});

//Lupa Password
Route::prefix('password')->group(function () {
    Route::post('forgot', [ResetPasswordController::class, 'forgotPassword']);
    Route::get('validate/{token}', [ResetPasswordController::class, 'validateToken']);
    Route::put('reset/{resetToken}', [ResetPasswordController::class, 'resetPassword']);
});

//Produk
Route::middleware(['auth.jwt'])->group(function(){
    Route::get('produk/', [ProdukController::class, 'index']);
    Route::get('produk/{id}', [ProdukController::class, 'show']);
    // Route::get('/produk-variasi/{id_variation}', [ProdukController::class, 'showVariation']);
});
Route::middleware(['auth.jwt', 'jwt.role:admin'])->group(function(){
    Route::post('produk/tambah/', [ProdukController::class, 'store']);
    Route::put('produk/edit/{id}', [ProdukController::class, 'updated']);
    Route::delete('produk/{id}', [ProdukController::class, 'destroy']);
});

//Simpan Data Raja Ongkir Di Database
Route::prefix('alamat')->group(function () {
    Route::get('ambil-data', [AlamatController::class, 'ambilData']);
    Route::get('provinsi', [AlamatController::class, 'getProvinsiList']);
    Route::get('kabupaten/{id_provinsi}', [AlamatController::class, 'getKabupatenByProvinsi']);
    Route::get('kode-pos/{id_kota}', [AlamatController::class, 'getKodePosByKabupaten']);
});

//Alamat Pelanggan
Route::middleware(['auth.jwt', 'jwt.role:pelanggan'])->group(function(){
    Route::post('/addresses', [AlamatDetailController::class, 'store']);
    Route::put('/addresses/{id_alamat}', [AlamatDetailController::class, 'update']);
    Route::delete('/addresses/{id_alamat}', [AlamatDetailController::class, 'destroy']);
    Route::get('/alamat', [AlamatDetailController::class, 'getAlamatByPelanggan']);

    Route::get('/provinces/{id_provinsi}/cities', [AlamatDetailController::class, 'getCitiesByProvince']);
    Route::get('/cities/{id_kota}/postal-codes', [AlamatDetailController::class, 'getPostalCodesByCity']);
});
Route::get('/alamat/{id_pelanggan}', [AlamatDetailController::class, 'getAlamatByIdPelanggan'])->middleware('auth.jwt', 'jwt.role:admin,pemilik_toko');

//Pemesanan
Route::middleware(['auth.jwt'])->group(function(){
    //Keranjang
    Route::get('/keranjang', [PemesananController::class, 'GetKeranjang']);
    Route::post('/keranjang/tambah', [PemesananController::class, 'TambahKeKeranjang']);
    Route::put('/keranjang/update/{IdDetail}', [PemesananController::class, 'UpdateItemKeranjang']);
    Route::delete('/keranjang/delete/{IdDetail}', [PemesananController::class, 'DeleteItemKeranjang']);
    //Ongkir
    Route::post('/pilih-alamat-pengiriman', [PengirimanController::class, 'pilihAlamatPengiriman']);
    Route::get('/opsi-pengiriman', [PengirimanController::class, 'getOpsiPengiriman']);
    Route::post('/pilih-jasa', [PengirimanController::class, 'pilihJasaPengiriman']);
    //Payment Gateway
    Route::post('payments/create/{id_pemesanan}', [PembayaranController::class, 'createPayment']);
    Route::get('/payments/get-token/{id_pemesanan}', [PembayaranController::class, 'getSnapToken']);
    //Status Pengiriman
    Route::put('pengiriman/dikirim/{id_pengiriman}', [PengirimanController::class, 'updateStatusDikirim']);
    Route::put('pengiriman/diterima/{id_pengiriman}', [PengirimanController::class, 'updateStatusDiterima']);
    //Ulasan Pelanggan
    Route::post('ulasan/buat', [UlasanController::class, 'storeUlasan']);
});

//Voucher
Route::middleware(['auth.jwt'])->group(function(){
    Route::prefix('vouchers')->group(function () {
        Route::post('/', [VoucherController::class, 'store']);
        Route::put('/{id}', [VoucherController::class, 'update']);
        Route::delete('/{id}', [VoucherController::class, 'destroy']);
        Route::post('/distribusi', [VoucherController::class, 'distribusiVoucher']);
        Route::post('/gunakan', [VoucherController::class, 'gunakanVoucher']);
        Route::get('/active', [VoucherController::class, 'getActiveVouchersForCustomer']);
        Route::get('/tersedia/{idPelanggan}', [VoucherController::class, 'getVoucherTersedia']);
        Route::get('/', [VoucherController::class, 'getAllVouchers']);
        Route::get('/active/all', [VoucherController::class, 'getAllActiveVouchers']);
    });
});

//PaymentGateway
Route::post('payments/callback', [PembayaranController::class, 'callback']);

//Ulasan
Route::get('ulasan/get-by-produk/{id_produk}', [UlasanController::class, 'getUlasanProduk']);
Route::post('ulasan/balasan/{id_ulasan}', [UlasanController::class, 'SimpanBalasan'])->middleware('auth.jwt', 'jwt.role:admin,pemilik_toko');

//Laporan 
Route::post('/laporan/penjualan', [LaporanController::class, 'getLaporanPenjualan'])->middleware('auth.jwt', 'jwt.role:admin,pemilik_toko');

//Ekstensi + Coba Coba
Route::post('upload-gambar', [GambarController::class, 'uploadGambar']);
Route::get('ambil-data', [AlamatController::class, 'ambilData']);