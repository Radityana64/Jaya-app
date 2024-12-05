<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SavePreviousUrl
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah pengguna sudah login
        if ($request->user()) {
            \Log::info('User  is already logged in, redirecting to dashboard.');
            return redirect()->route('produk.grids');
        }

        // Cek apakah session 'previous_url' sudah ada dan request adalah ke halaman login
        if (!session()->has('previous_url') && $request->is('login')) {
            $previousUrl = url()->previous();

            // Validasi URL
            if ($this->isValidUrl($previousUrl, $request)) {
                session(['previous_url' => $previousUrl]);
            }
        }

        return $next($request);
    }

    // Fungsi untuk memvalidasi URL
    protected function isValidUrl($url, Request $request)
    {
        // Pastikan URL adalah bagian dari domain yang sama
        return filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_HOST) === $request->getHost();
    }
}