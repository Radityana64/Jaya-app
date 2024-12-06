<?php

namespace App\Http\Controllers\Api;

use App\Models\Pelanggan;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'username' => 'required|string|max:255',
            'telepon' => 'required|string|max:15|unique:tb_pelanggan',
            'email'=>'required|string|email|max:255|unique:tb_users',
            'password'=> 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 400);
        }

        \DB::beginTransaction();

        try{
            $user = User::create([
                'email' => $request->email,
                'password' => $request->password, // Hash is handled by model mutator
                'role' => 'pelanggan',
            ]);
            $pelanggan = Pelanggan::create([
                'id_user' => $user->id_user,
                'username' => $request->username,
                'telepon' => $request->telepon
            ]);
            
            \DB::commit();

            $user->load('pelanggan');
            return response()->json([
                'message' => 'Registration successful',
                'user' => $user
            ], 201);

        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['error'=>'Registrasi gagal'], 500);
        }
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 400);            
        }
        try {
            $user = User::where('email', $request->email)->first();
                
                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User not found'
                    ], 404);
                }

            $credentials = $request->only('email', 'password');
            
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            $user = Auth::user(); 
            
            $response = [
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                    'user' => [
                        'id' => $user->id_user,
                        'nama_lengkap' => $user->nama_lengkap,
                        'email' => $user->email,
                        'role' => $user->role,
                        'tanggal_dibuat' => $user->tanggal_dibuat,
                        'tanggal_diperbarui' => $user->tanggal_diperbarui
                    ]
                ]
            ];
            return response()->json($response, 200);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token',
                'error_detail' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getPelanggan(Request $request)
    {
        try {
            $user = auth()->user();

            $dataPelanggan = User::with('pelanggan')
                ->where('id_user', $user->id_user)
                ->firstOrFail();

            return response()->json([           
                'data'=>$dataPelanggan,
            ]);            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Pelanggan not found'], 404);
        }
    }

    public function getMasterPelanggan()
    {
        try {
            // Ambil seluruh data pelanggan dengan data relasi user
            $pelanggan = Pelanggan::with('user')->get();

            return response()->json(['pelanggan' => $pelanggan]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data pelanggan'], 500);
        }
    }

    public function updateProfil(Request $request)
    {
        try {
            // Dapatkan pelanggan yang sedang login
            $pelanggan = Auth::user()->pelanggan;

            // Validasi input
            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:tb_users,email,' . Auth::id() . ',id_user',
                'username' => 'sometimes|string|unique:tb_pelanggan,username,' . $pelanggan->id_pelanggan . ',id_pelanggan',
                'telepon' => 'sometimes|string|max:20',
            ]);

            // Jika validasi gagal
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update User
            $user = Auth::user();
            $userUpdateData = [];
            
            if ($request->has('nama_lengkap')) {
                $userUpdateData['nama_lengkap'] = $request->nama_lengkap;
            }
            
            if ($request->has('email')) {
                $userUpdateData['email'] = $request->email;
            }

            // Update user jika ada data
            if (!empty($userUpdateData)) {
                $user->update($userUpdateData);
            }

            // Update Pelanggan
            $pelangganUpdateData = [];
            
            if ($request->has('username')) {
                $pelangganUpdateData['username'] = $request->username;
            }
            
            if ($request->has('telepon')) {
                $pelangganUpdateData['telepon'] = $request->telepon;
            }

            // Update pelanggan jika ada data
            if (!empty($pelangganUpdateData)) {
                $pelanggan->update($pelangganUpdateData);
            }

            // Refresh data
            $user->refresh();
            $pelanggan->refresh();

            // Siapkan response
            $responseData = [
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'user' => [
                    'id_user' => $user->id_user,
                    'nama_lengkap' => $user->nama_lengkap,
                    'email' => $user->email,
                    'username' => $pelanggan->username,
                    'telepon' => $pelanggan->telepon
                ]
            ];

            return response()->json($responseData);

        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Update Profil Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }
}
