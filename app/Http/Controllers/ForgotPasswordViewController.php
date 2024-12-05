<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ForgotPasswordViewController extends Controller
{
    protected $apiBaseUrl;

    public function __construct() {
        $this->apiBaseUrl = env('API_BASE_URL', 'http://127.0.0.1:8000');
    }

    public function formEmail(){
        return view('auth.forgot-password');
    }
    
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Mengirimkan request ke API untuk mengirimkan link reset password
            $response = Http::post($this->apiBaseUrl.'/api/password/forgot', [
                'email' => $request->email,
            ]);

            // Mengambil data dari response
            $responseData = $response->json();

            if ($response->successful()) {
                return redirect()->back()->with('status', 'Link reset password telah dikirim ke email Anda.');
            } else {
                $errorMessage = $responseData['message'] ?? 'Terjadi kesalahan saat mengirim link reset password.';
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['email' => 'Pengiriman link reset password gagal: ' . $e->getMessage()]);
        }
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.form-reset', ['token' => $token]);
    }

    // Memproses permintaan reset password
    public function resetPassword(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Mengirimkan request ke API untuk mereset password menggunakan metode PUT
            $response = Http::put($this->apiBaseUrl.'/api/password/reset/'.$request->token, [
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ]);

            // Mengambil data dari response
            $responseData = $response->json();

            if ($response->successful()) {
                return redirect()->route('login')->with('status', 'Password berhasil direset! Silakan login dengan password baru Anda.');
            } else {
                $errorMessage = $responseData['message'] ?? 'Terjadi kesalahan saat mereset password.';
                return redirect()->back()->withErrors(['api' => $errorMessage])->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['api' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }
}
