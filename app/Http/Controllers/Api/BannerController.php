<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    // Create a new banner
    public function create(Request $request)
    {
        $requiredFields = ['judul', 'gambar_banner', 'status'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'gambar_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Jika validasi gagal, kembalikan respons dengan kesalahan
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Upload gambar ke Cloudinary
            $uploadedFile = Cloudinary::upload($request->file('gambar_banner')->getRealPath(), [
                'folder' => 'banner_images'
            ]);

            // Dapatkan URL gambar yang bersih
            $cleanUrl = preg_replace('/\.[^.]+$/', '', $uploadedFile->getSecurePath());

            // Buat banner baru
            $banner = Banner::create([
                'judul' => $request->judul,
                'gambar_banner' => $cleanUrl,
                'deskripsi' => $request->deskripsi,
                'status' => $request->status,
            ]);

            // Kembalikan respons sukses
            return response()->json([
                'status' => true,
                'message' => 'Berhasil membuat banner',
                'data' => $banner
            ], 201);

        } catch (\Exception $e) {
            // Tangani kesalahan yang mungkin terjadi
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat membuat banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update an existing banner
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return  response()->json([
                'status' => false,
                'message' => 'Banner not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|required|string|max:255',
            'gambar_banner' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update image if provided
        if ($request->hasFile('gambar_banner')) {
            $uploadedFile = Cloudinary::upload($request->file('gambar_banner')->getRealPath(), [
                'folder' => 'banner_images'
            ]);

            $cleanUrl = preg_replace('/\.[^.]+$/', '', $uploadedFile->getSecurePath());
            $banner->gambar_banner = $cleanUrl;
        }

        // Update other fields
        if ($request->has('judul')) {
            $banner->judul = $request->judul;
        }
        if ($request->has('deskripsi')) {
            $banner->deskripsi = $request->deskripsi;
        }
        if ($request->has('status')) {
            $banner->status = $request->status;
        }

        $banner->save();

        return response()->json($banner, 200);
    }

    // Deactivate a banner
    public function deactivate($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return  response()->json([
                'status' => false,
                'message' => 'Banner not found.'
            ], 404);
        }
        $banner->status = 'nonaktif';
        $banner->save();

        return response()->json($banner, 200);
    }

    // Get all active banners
    public function getActiveBanners()
    {
        try {
            $banners = Banner::where('status', 'aktif')->get();

            if ($banners->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No active banners found.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $banners
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching banners.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getActiveBannersById($id)
    {
        try {
            $banner = Banner::where('status', 'aktif')->find($id);
            if (!$banner) {
                return  response()->json([
                    'status' => false,
                    'message' => 'Banner not found.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $banner
            ], 200);

            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }    
}