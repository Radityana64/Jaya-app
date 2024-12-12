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
        $banner = Banner::findOrFail($id);

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
        $banner = Banner::findOrFail($id);
        $banner->status = 'nonaktif';
        $banner->save();

        return response()->json($banner, 200);
    }

    // Get all active banners
    public function getActiveBanners()
    {
        $banners = Banner::where('status', 'aktif')->get();
        
        return response()->json([
            'status'=>true,
            'data'=>$banners   
        ], 200);
    }

    public function getActiveBannersById($id)
    {
        $bannerId = Banner::where('status', 'aktif')
        ->findOrFail($id);
        return response()->json($bannerId, 200);
    }
}