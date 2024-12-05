<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranViewController;
use App\Http\Controllers\AuthViewController;
use App\Http\Controllers\ForgotPasswordViewController;
use App\Http\Controllers\ProfilViewController;

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
    return view('frontend.pages.product-grids');
})->name('produk.grids');

// Route::get('/etalase/produk', function () {
//     return view('frontend.pages.product-grids');
// })->name('produk.grids');

Route::get('/tesgambar', function () {
    return view('users.beranda');
});

// routes/web.php
Route::get('/reset-password/{token}', function ($token) {
    return view('emails.form-reset', ['token' => $token]);
});

Route::get('/dashboard', function(){
    return view('dashboard');
})->name('dashboard');

Route::get('/welcome', function(){
    return view('welcome');
})->name('welcome');

Route::get('/register', [AuthViewController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthViewController::class, 'register'])->name('register.submit');

Route::middleware(['save.previous.url'])->group(function () {
    Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
    
});
Route::post('/login', [AuthViewController::class, 'login'])->name('login.submit');

Route::get('/forgot/password', [ForgotPasswordViewController::class, 'formEmail'])->name('forgot.password');
Route::post('/password/forgot', [ForgotPasswordViewController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [ForgotPasswordViewController::class, 'showResetPasswordForm'])->name('password.reset');
Route::put('/password/reset/', [ForgotPasswordViewController::class, 'resetPassword'])->name('password.update');

Route::get('/beranda', function(){
    return view('pelanggan.beranda');
})->name('beranda');

Route::get('/produk-detail/{id}', function(){
    return view('frontend.pages.product_detail');
})->name('produk.detail');

Route::get('/keranjang', function(){
    return view('frontend.pages.cart');
})->name('keranjang');

Route::get('/checkout', function(){
    return view('frontend.pages.checkout');
})->name('checkout');

Route::get('/data-pelanggan', [ProfilViewController::class, 'index'])
    ->name('pelanggan.app');

Route::get('/data-pelanggan/{page}', [ProfilViewController::class, 'loadPage'])
    ->name('pelanggan.page')
    ->where('page', 'profil|alamat|voucher|pesanan');

// Route::get('payment/success/{id}', [PembayaranViewController::class, 'showSuccessPage'])->name('payment.success');
// Route::get('payment/failed/{id}', [PembayaranViewController::class, 'showFailedPage'])->name('payment.failed');
// Route::get('payment/waiting/', [PembayaranViewController::class, 'showWaitingPage'])->name('payment.waiting');

Route::get('/admin', function(){return view('backend.index');})->name('admin');

Route::get('/kategori', function(){return view('backend.category.index');})->name('index.kategori');
Route::get('/kategori/create', function(){return view('backend.category.create');})->name('category.create');

Route::get('/produk', function(){return view('backend.product.index');})->name('index.produk');

Route::get('/pemesanan', function(){return view('backend.order.index');});

Route::get('/ulasan', function(){return view('backend.review.index');});

Route::get('/pelanggan', function(){return view('bakcend.users.index');});