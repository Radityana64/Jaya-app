<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranViewController;
use App\Http\Controllers\ResetPasswordViewController;

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
    return view('login');
});

// routes/web.php
Route::get('/reset-password/{token}', function ($token) {
    return view('emails.form-reset', ['token' => $token]);
});

Route::get('/dashboard', function(){
    return view('dashboard');
});


Route::get('payment/success/{id}', [PembayaranViewController::class, 'showSuccessPage'])->name('payment.success');
Route::get('payment/failed/{id}', [PembayaranViewController::class, 'showFailedPage'])->name('payment.failed');
Route::get('payment/waiting/', [PembayaranViewController::class, 'showWaitingPage'])->name('payment.waiting');

// Route::get('/forgot-password', [ResetPasswordViewController::class, 'showForgotForm'])->name('password.request');
// Route::get('/reset-password', [ResetPasswordViewController::class, 'showResetForm'])->name('password.reset');