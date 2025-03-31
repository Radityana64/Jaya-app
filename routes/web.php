<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PembayaranViewController;
use App\Http\Controllers\AuthViewController;
use App\Http\Controllers\ForgotPasswordViewController;
use App\Http\Controllers\ProfilViewController;
use App\Http\Controllers\LaporanViewController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('frontend.index');
})->name('index');

Route::get('/about-us', function () {
    return view('frontend.pages.about-us');
})->name('about-us');
//Auth
Route::get('/register', [AuthViewController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthViewController::class, 'register'])->name('register.submit');

Route::middleware(['save.previous.url'])->group(function () {
    Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
    
});
Route::post('/login', [AuthViewController::class, 'login'])->name('login.submit');

Route::get('/forgot/password', [ForgotPasswordViewController::class, 'formEmail'])->name('forgot.password');
Route::post('/password/forgot', [ForgotPasswordViewController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [ForgotPasswordViewController::class, 'showResetPasswordForm'])->name('password.reset');
Route::put('/reset/password/{token}', [ForgotPasswordViewController::class, 'PasswordReset'])->name('password.update');

//=================================================
Route::get('/etalase/produk', function () {
    return view('frontend.pages.product-grids');
})->name('produk.grids');

Route::get('/produk-detail/{id}', function(){
    return view('frontend.pages.product_detail');
})->name('produk.detail');

//Pelanggan=========================================================================================================
Route::middleware(['auth', 'role:pelanggan'])->group(function () {
    Route::get('/keranjang', function () { return view('frontend.pages.cart'); })->name('keranjang');
    Route::get('/checkout', function () { return view('frontend.pages.checkout'); })->name('checkout');

    Route::get('/data-pelanggan', [ProfilViewController::class, 'index'])->name('pelanggan.app');
    Route::get('/data-pelanggan/{page}', [ProfilViewController::class, 'loadPage'])
        ->name('pelanggan.page')
        ->where('page', 'profil|alamat|voucher|pesanan');
});
    
//Admin=============================================================================================================
Route::middleware(['auth', 'role:admin,pemilik_toko'])->group(function () {
    Route::get('/admin', function () { return view('backend.index'); })->name('admin');

    // Kategori
    Route::get('/kategori', function () { return view('backend.category.index'); })->name('index.kategori');
    Route::get('/kategori/create', function () { return view('backend.category.create'); })->name('category.create');
    Route::get('/kategori/edit/{id}', function () { return view('backend.category.edit'); })->name('category.edit');

    // Produk
    Route::get('/produk', function () { return view('backend.product.index'); })->name('index.produk');
    Route::get('/produk/create', function () { return view('backend.product.create'); })->name('produk.create');
    Route::get('/produk/edit/{id}', function () { return view('backend.product.edit'); })->name('produk.edit');
    Route::get('/ulasan/{id}', function () { return view('backend.product.ulasan'); });

    // Pemesanan
    Route::get('/pemesanan', function () { return view('backend.order.index'); });

    // Voucher
    Route::get('/voucher', function () { return view('backend.coupon.index'); });
    Route::get('/voucher/create', function () { return view('backend.coupon.create'); })->name('voucher.create');
    Route::get('/voucher/edit/{id}', function () { return view('backend.coupon.edit'); })->name('voucher.edit');

    // Pelanggan
    Route::get('/pelanggan', function () { return view('backend.users.index'); });
    Route::get('/pelanggan/detail-pesanan/{id}', function () { return view('backend.users.pesanan'); });

    // Banner
    Route::get('/banner', function () { return view('backend.banner.index'); });
    Route::get('/banner/create', function () { return view('backend.banner.create'); })->name('banner.create');
    Route::get('/banner/edit/{id}', function () { return view('backend.banner.edit'); })->name('banner.edit');
});

Route::middleware(['auth', 'role:pemilik_toko'])->group(function () {
    Route::get('/laporan', function () { return view('backend.Laporan.index'); });
    Route::get('/laporan/grafik', function () { return view('backend.Laporan.grafik'); })->name('laporan.grafik');

    Route::get('/data/admin', function () { return view('backend.admin.index'); });
    Route::get('/data/admin/create', function () { return view('backend.admin.create'); })->name('admin.create');
});

Route::middleware(['auth:web'])->group(function(){
    Route::post('/logout/session', [AuthViewController::class, 'webLogout']);
});


//Testing============================================
Route::get('/tesgambar', function () {
    return view('users.beranda');
});

Route::get('/dashboard', function(){
    return view('dashboard');
})->name('dashboard');

Route::get('/welcome', function(){
    return view('welcome');
})->name('welcome');

Route::get('/beranda', function(){
    return view('pelanggan.beranda');
})->name('beranda');

Route::get('/reset-password/{token}', function ($token) {
    return view('emails.form-reset', ['token' => $token]);
});
