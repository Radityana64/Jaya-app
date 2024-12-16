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
        $this->apiBaseUrl = env('API_BASE_URL', 'http://127.0.0.1:8000');
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
                // Jika respons tidak berhasil, ambil pesan kesalahan dari API
                $responseData = $response->json();
                $errorMessage = $responseData['meta']['message'] ?? 'Registrasi Gagal';

                // Jika ada kesalahan validasi, ambil detail kesalahan
                if (isset($responseData['error'])) {
                    $errors = $responseData['error'];
                    return back()->withErrors($errors)->withInput();
                }

                // Jika tidak ada detail kesalahan, lemparkan exception
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
        // Validasi input dari request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        try {
            // Mengirimkan request ke API untuk login
            $response = Http::post($this->apiBaseUrl . '/api/user/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            // Mengambil data dari response
            $responseData = $response->json();

            // Mengecek apakah response berhasil dan token ada
            if ($response->successful() && isset($responseData['data']['token'])) {
                
                // Menyimpan token ke session
                session(['auth.token' => $responseData['data']['token']]);
                session(['user_info' => $responseData['data']['user']]);

                // $previousUrl = session('previous_url', route('index'));
                // if ($previousUrl === route('login')) {
                //     $previousUrl = route('index'); // Redirect ke index jika previous URL adalah login
                // }
                
                // session()->forget('previous_url');
                // Log::info('Redirecting to: ' . $previousUrl);

                // Redirect
                return redirect()->route('index');
                
            } else {
                // Jika tidak berhasil, ambil pesan error dari response
                $errorMessage = $responseData['message'] ?? 'Invalid credentials or failed to get token from API response.';
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            // Menangani error dan mengembalikan pesan error
            return redirect()->back()->withErrors(['login' => 'Login failed: ' . $e->getMessage()]);
        }
    }
}
