<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ResetPasswordController extends Controller
{
    protected $appBaseUrl;

    public function __construct() {
        $this->appBaseUrl = config('services.app_base_url');
    }
    public function forgotPassword(Request $request)
    {
        if (empty($request->all())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. No data provided.',
            ], 400); // 400 Bad Request
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email tidak ditemukan',
                ], 404); // 404 Not Found
            }        
            
            // Generate JWT token
            $token = JWTAuth::customClaims(['exp' => now()->addHour()->timestamp])
                            ->fromUser($user);

            // Generate reset URL dengan JWT token
            $resetUrl = rtrim($this->appBaseUrl, '/') . "/password/reset/{$token}";

            // Kirim email
            Mail::send('auth.send-email', ['resetUrl' => $resetUrl], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Reset Password Request');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Link reset password telah dikirim ke email Anda'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses permintaan',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function validateResetToken($token)
    {
        try {
            $user = JWTAuth::setToken($token)->authenticate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Token valid',
                'email' => $user->email
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau sudah kadaluarsa',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }

    public function resetPassword(Request $request, $token)
    {
        $requiredFields = ['password', 'password_confirmation'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();

            // Update password
            $user->password = $request->password;
            $user->save();

            // Invalidate token setelah digunakan
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil direset'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau sudah kadaluarsa',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }
}