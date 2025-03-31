<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Pelanggan;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Authenticate user and get the authenticated user
            $user = JWTAuth::parseToken()->authenticate();

            // Cek apakah user ditemukan
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if ($user->role === 'pelanggan') {
                $pelanggan = Pelanggan::where('id_user', $user->id_user)->first();
                
                if ($pelanggan && $pelanggan->status !== 'aktif') {
                    // 🔥 Invalidasi token pelanggan ini
                    JWTAuth::invalidate(JWTAuth::getToken());

                    return response()->json([
                        'error' => 'Akun Anda telah dinonaktifkan oleh admin. Silakan login kembali.'
                    ], 403);
                }
            }
            if (!empty($roles) && !in_array($user->role, $roles)) {
                return response()->json(['error' => 'Forbidden Unauthorized access'], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Authorization Token not found'], 401);
        }

        // Tambahkan user ke request
        $request->merge(['user' => $user]);

        return $next($request);
    }
}