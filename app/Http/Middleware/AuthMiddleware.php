<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Authenticate user and get the authenticated user
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user exists
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Check if user has the required role
            if (!empty($roles) && !in_array($user->role, $roles)) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Authorization Token not found'], 401);
        }

        // Add user to the request
        $request->merge(['user' => $user]);

        return $next($request);
    }
}