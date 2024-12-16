<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SavePreviousUrl
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->is('login', 'register', 'password.reset', 'forgot.password', 'logout')) {
            $previousUrl = url()->previous();

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