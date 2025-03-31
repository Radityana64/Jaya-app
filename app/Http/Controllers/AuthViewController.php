<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client; 

class AuthViewController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        // Set base URL API dari environment variable
        $this->apiBaseUrl = config('services.api_base_url');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'telepon' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            $response = Http::post($this->apiBaseUrl.'/api/pelanggan/register', [
                'username' => $request->username,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                // Jika registrasi berhasil
                return redirect()->route('login')->with('status', 'Registrasi Success');
            } else {
                // Jika respons tidak berhasil, ambil data dari response JSON
                $responseData = $response->json();
                $errorMessage = $responseData['message'] ?? 'Registrasi Gagal';

                // Cek apakah ada error validasi dari API (misalnya format seperti yang Anda berikan)
                if (isset($responseData['status']) && $responseData['status'] === 'error' && isset($responseData['errors'])) {
                    // Kembalikan ke halaman sebelumnya dengan pesan error dari API
                    return back()->withErrors($responseData['errors'])->withInput();
                }

                // Jika tidak ada error validasi spesifik, lempar exception dengan pesan umum
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            // Mengalihkan kembali dengan pesan kesalahan jika terjadi pengecualian
            return back()->withErrors(['message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        try {
            // Panggil API untuk autentikasi
            $response = Http::post($this->apiBaseUrl . '/api/user/login', $credentials);
            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                // Jika login berhasil
                $request->session()->regenerate();

                // Simpan token dan user info ke session
                session(['auth.token' => $responseData['data']['token']]);
                session(['user_info' => $responseData['data']['user']]);

                if (Auth::guard('web')->attempt($credentials)) {
                    $request->session()->regenerate();
                }
                return redirect()->route('index');
            } else {
                // Tangani error berdasarkan status kode HTTP dan response JSON
                $errorMessage = $responseData['message'] ?? 'Login failed';

                // Error validasi: Jika ada 'errors'
                if (isset($responseData['status']) && $responseData['status'] === 'error' && isset($responseData['errors'])) {
                    return redirect()->back()->withErrors($responseData['errors'])->withInput();
                }

                // Error umum: Jika ada 'message' (misalnya akun nonaktif atau pengguna tidak ditemukan)
                if (isset($responseData['status']) && $responseData['status'] === 'error' && isset($responseData['message'])) {
                    return redirect()->back()->withErrors(['login' => $responseData['message']])->withInput();
                }

                // Error kredensial salah: Jika ada 'error' (tanpa 'status')
                if (isset($responseData['error'])) {
                    return redirect()->back()->withErrors(['login' => $responseData['error']])->withInput();
                }

                // Fallback untuk error lain
                return redirect()->back()->withErrors(['login' => $errorMessage])->withInput();
            }
        } catch (\Exception $e) {
            // Jika terjadi exception (misalnya API tidak respons)
            \Log::warning('Login error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['login' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function webLogout(Request $request)
    {
        \Log::info('Web Logout called', ['session' => session()->all()]);
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'Web session logged out successfully'
        ]);
    }
}
