<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GambarProduk;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GambarController extends Controller
{
    public function uploadGambar(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'id_produk' => 'required|integer|exists:tb_produk,id_produk',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Upload gambar ke Cloudinary
        $image = $request->file('gambar');
        
        try {
            // Generate unique filename
            $filename = Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();

            // Logging untuk debugging
            \Log::info('Cloudinary Upload Attempt', [
                'file' => $filename,
                'mime' => $image->getMimeType(),
                'size' => $image->getSize(),
            ]);

            // Pastikan file valid
            if (!$image->isValid()) {
                \Log::error('Invalid image file', [
                    'error' => $image->getError()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'File gambar tidak valid'
                ], 400);
            }

            // Upload dengan opsi tambahan
            $uploadOptions = [
                'folder' => 'produk_images',
                'public_id' => pathinfo($filename, PATHINFO_FILENAME),
                'overwrite' => true,
                'resource_type' => 'image',
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'auto'
                ]
            ];

            $uploadResponse = Cloudinary::upload(
                $image->getRealPath(), 
                $uploadOptions
            );

            // Pastikan mendapatkan URL yang valid
            $uploadedFileUrl = $uploadResponse->getSecurePath();

            if (empty($uploadedFileUrl)) {
                \Log::error('Empty Cloudinary URL', [
                    'upload_result' => $uploadResponse,
                    'options' => $uploadOptions
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mendapatkan URL gambar'
                ], 500);
            }

            // Menyimpan informasi gambar ke database
            $gambarProduk = GambarProduk::create([
                'id_produk' => $request->id_produk,
                'gambar' => $uploadedFileUrl,
                'public_id' => $uploadResponse->getPublicId(),
            ]);

            // Menampilkan respon sukses
            return response()->json([
                'status' => 'success',
                'message' => 'Gambar berhasil diupload',
                'data' => $gambarProduk,
            ], 201);

        } catch (\Exception $e) {
            // Log error secara detail
            \Log::error('Cloudinary Upload Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengupload gambar',
                'error' => config('app.debug') ? $e->getMessage() : 'Kesalahan internal'
            ], 500);
        }
    }

    // Tambahan metode untuk menghapus gambar
    public function deleteGambar($id)
    {
        try {
            $gambarProduk = GambarProduk::findOrFail($id);

            // Hapus dari Cloudinary
            if (!empty($gambarProduk->public_id)) {
                Cloudinary::destroy($gambarProduk->public_id);
            }

            // Hapus dari database
            $gambarProduk->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Gambar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal menghapus gambar', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus gambar'
            ], 500);
        }
    }
}