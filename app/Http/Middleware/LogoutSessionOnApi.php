<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LogoutSessionOnApi
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Cek jika request adalah ke /api/logout
        if ($request->is('api/logout')) {
            // Logout dari guard web (menghapus sesi)
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $response;
    }
}
