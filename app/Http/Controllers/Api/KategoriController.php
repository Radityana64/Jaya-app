<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    public function getKategori()
    {
        try {
            // Ambil kategori utama (level 1)
            $kategoris = Kategori::where('level', '1')
                ->with(['subKategori' => function($query) {
                    $query->where('status', 'aktif');
                }])
                ->where('status', 'aktif')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendapatkan kategori',
                'data' => $kategoris
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function getKategoriNonaktif()
    // {
    //     try {
    //         // Ambil kategori utama (level 1)
    //         $kategoris = Kategori::where('level', '1')
    //             ->with(['subKategori' => function($query) {
    //                 $query->where('status', 'nonaktif');
    //             }])
    //             ->where('status', 'aktif')
    //             ->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Berhasil mendapatkan kategori',
    //             'data' => $kategoris
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Gagal mendapatkan kategori: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getKategoriById($id_kategori)
    {
        try {
            // Ambil kategori dengan relasi yang sesuai
            $kategori = Kategori::where('id_kategori', $id_kategori)
                ->where('status', 'aktif')
                ->first();
    
            // Periksa apakah kategori ditemukan
            if (!$kategori) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }
    
            // Logika loading berdasarkan level
            switch ($kategori->level) {
                case '1':
                    // Untuk level 1, muat semua sub-kategori (level 2)
                    $kategori->load([
                        'subKategori' => function($query) {
                            $query->where('status', 'aktif');
                        }
                    ]);
                    break;
    
                case '2':
                    // Untuk level 2, muat induk (level 1) 
                    // Dan kategori level 2 lainnya dari induk yang sama
                    $kategori->load([
                        'induk', // Load induk kategori
                        'induk.subKategori' => function($query) {
                            $query->where('status', 'aktif');
                        }
                    ]);
                    break;
    
                default:
                    // Tangani level yang tidak dikenal
                    return response()->json([
                        'status' => false,
                        'message' => 'Level kategori tidak valid'
                    ], 400);
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendapatkan kategori',
                'data' => $kategori
            ], 200);
    
        } catch (\Exception $e) {
            // Log error untuk kebutuhan debugging
            \Log::error('Error getting kategori: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendapatkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Tambah/Edit Kategori
    public function createKategori(Request $request)
    {
        $requiredFields = ['nama_kategori', 'gambar_kategori', 'status'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'gambar_kategori' => 'image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,nonaktif',
            'sub_kategori' => 'nullable|array',
            'sub_kategori.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Buat atau update kategori utama
            $kategori = new Kategori();
            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->level = '1'; // Kategori utama selalu level 1
            $kategori->status = $request->status;

            // Proses upload gambar
            if ($request->hasFile('gambar_kategori')) {
                $uploadedFile = Cloudinary::upload($request->file('gambar_kategori')->getRealPath(), [
                    'folder' => 'kategori_images'
                ]);

                $cleanUrl = preg_replace('/\.[^.]+$/', '', $uploadedFile->getSecurePath());
                $kategori->gambar_kategori = $cleanUrl;
            }

            // Simpan kategori utama
            $kategori->save();

            // Proses sub kategori jika ada
            $subKategoris = [];
            if ($request->has('sub_kategori') && 
                is_array($request->sub_kategori) && 
                !empty(array_filter($request->sub_kategori))) {
                
                foreach ($request->sub_kategori as $namaSubKategori) {
                    // Skip jika nama sub kategori kosong
                    if (trim($namaSubKategori) === '') continue;

                    // Cek apakah sub kategori sudah ada
                    $subKategori = Kategori::where('nama_kategori', $namaSubKategori)
                        ->where('level', '2')
                        ->where('id_induk', $kategori->id_kategori)
                        ->first();

                    // Jika belum ada, buat sub kategori baru
                    if (!$subKategori) {
                        $subKategori = new Kategori();
                        $subKategori->nama_kategori = $namaSubKategori;
                        $subKategori->level = '2';
                        $subKategori->id_induk = $kategori->id_kategori;
                        $subKategori->status = $request->status; // Inherit status dari kategori utama
                        $subKategori->save();
                    }

                    $subKategoris[] = $subKategori;
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil menyimpan kategori',
                'data' => [
                    'kategori' => $kategori,
                    'sub_kategori' => $subKategoris
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateKategori(Request $request, $id_kategori)
    {
        // Cari kategori utama
        $kategori = Kategori::find($id_kategori);
        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        // Cek jika tidak ada data yang relevan yang diberikan
        if ($request->isNotFilled(['nama_kategori', 'gambar_kategori', 'status', 'sub_kategori', 'new_sub_kategori'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. No data provided.',
            ], 400); // 400 Bad Request
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'sometimes|string|max:255',
            'gambar_kategori' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'sometimes|in:aktif,nonaktif',
            'sub_kategori' => 'nullable|array',
            'sub_kategori.*.id' => 'sometimes|exists:tb_kategori,id_kategori',
            'sub_kategori.*.nama' => 'sometimes|string|max:255',
            'sub_kategori.*.status' => 'sometimes|in:aktif,nonaktif',
            'new_sub_kategori' => 'nullable|array',
            'new_sub_kategori.*' => 'string|max:255'
        ]);

        // Cek validasi
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unprocessable Entity. Validation failed.',
                'errors' => $validator->errors(),
            ], 422); // 422 Unprocessable Entity
        }

        DB::beginTransaction();
        try {
            // Update nama kategori jika ada perubahan
            if ($request->has('nama_kategori')) {
                $kategori->nama_kategori = $request->nama_kategori;
            }

            // Update status kategori jika ada perubahan
            if ($request->has('status')) {
                $kategori->status = $request->status;
            }

            // Proses upload gambar baru jika ada
            if ($request->hasFile('gambar_kategori')) {
                // Upload gambar baru
                $uploadedFile = Cloudinary::upload($request->file('gambar_kategori')->getRealPath(), [
                    'folder' => 'kategori_images'
                ]);

                $cleanUrl = preg_replace('/\.[^.]+$/', '', $uploadedFile->getSecurePath());
                $kategori->gambar_kategori = $cleanUrl;
            }

            // Simpan perubahan kategori utama
            $kategori->save();

            // Proses update sub kategori yang sudah ada
            if ($request->has('sub_kategori')) {
                foreach ($request->sub_kategori as $subKategoriData) {
                    $subKategori = Kategori::where('id_kategori', $subKategoriData['id'])
                        ->where('id_induk', $kategori->id_kategori)
                        ->where('level', '2')
                        ->first();

                    if (!$subKategori) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Sub kategori tidak ditemukan'
                        ], 404);
                    }

                    // Update nama sub kategori jika ada
                    if (isset($subKategoriData['nama'])) {
                        // Cek apakah nama sub kategori sudah ada di kategori yang sama
                        $existingSubKategori = Kategori::where('nama_kategori', $subKategoriData['nama'])
                            ->where('id_kategori', '!=', $subKategori->id_kategori)
                            ->exists();

                        if ($existingSubKategori) {
                            return response()->json([
                                'status' => false,
                                'message' => "Sub kategori dengan nama {$subKategoriData['nama']} sudah ada."
                            ], 409); // 409 Conflict
                        }

                        $subKategori->nama_kategori = $subKategoriData ['nama'];
                    }

                    // Update status sub kategori jika ada
                    if (isset($subKategoriData['status'])) {
                        $subKategori->status = $subKategoriData['status'];
                    }

                    $subKategori->save();
                }
            }

            // Proses tambah sub kategori baru
            if ($request->has('new_sub_kategori')) {
                $subKategoris = [];
                foreach ($request->new_sub_kategori as $namaSubKategori) {
                    // Skip jika nama sub kategori kosong
                    if (trim($namaSubKategori) === '') continue;

                    // Cek apakah sub kategori sudah ada
                    $existingSubKategori = Kategori::where('nama_kategori', $namaSubKategori)
                        ->where('id_kategori', '!=', $subKategori->id_kategori)
                        ->exists();

                    // Jika belum ada, buat sub kategori baru
                    if (!$existingSubKategori) {
                        $subKategori = new Kategori();
                        $subKategori->nama_kategori = $namaSubKategori;
                        $subKategori->level = '2';
                        $subKategori->id_induk = $kategori->id_kategori;
                        $subKategori->status = $kategori->status; // Inherit status dari kategori utama
                        $subKategori->save();

                        $subKategoris[] = $subKategori;
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "Sub kategori dengan nama {$namaSubKategori} sudah ada."
                        ], 409); // 409 Conflict
                    }
                }
            }

            DB::commit();

            // Ambil ulang kategori dengan sub kategori terbaru
            $kategoriWithSub = Kategori::with(['subKategori' => function($query) {
                $query->where('level', '2');
            }])->find($kategori->id_kategori);

            return response()->json([
                'status' => true,
                'message' => 'Berhasil mengupdate kategori',
                'data' => $kategoriWithSub
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate kategori: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateStatus(Request $request, $id_kategori)
    {
        $kategori = Kategori::find($id_kategori);
        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $requiredFields = ['status'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }
        // Validasi input
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:aktif,nonaktif',
        ]);
    
        // Cek validasi
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        DB::beginTransaction();
        try {
            // Cari kategori utama
            $kategori = Kategori::findOrFail($id_kategori);

            // Update status kategori utama
            $kategori->status = $request->status;
            $kategori->save();

            // Jika kategori level 1 dinonaktifkan, nonaktifkan semua subkategori
            if ($kategori->level == 1 && $request->status == 'nonaktif') {
                $kategori->subKategori()->update(['status' => 'nonaktif']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status kategori berhasil diperbarui',
                'data' => $kategori
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui status kategori: ' . $e->getMessage()
            ], 500);
        }
    }
}