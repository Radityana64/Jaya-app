<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\GambarProduk;
use App\Models\DetailProduk;
use App\Models\ProdukVariasi;
use App\Models\DetailProdukVariasi;
use App\Models\OpsiVariasi;
use App\Models\TipeVariasi;
use App\Models\GambarVariasi;
use App\Models\Kategori;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Mengambil semua produk tanpa relasi produk variasi
            $produk = Produk::with('kategori','gambarProduk')
            ->where('status', 'aktif')
            ->get();

            // Mengolah data untuk menambahkan harga terendah
            $produk->transform(function ($item) {
                // Mengambil variasi produk
                $variations = $item->produkVariasi;

                // Cek apakah variasi ada
                if ($variations->isNotEmpty()) {
                    // Mengambil harga terendah
                    $minPrice = $variations->min('harga');
                } else {
                    $minPrice = null; // Jika tidak ada variasi, bisa diset ke null atau 0
                }

                // Menambahkan harga terendah ke objek produk
                return [
                    'id_produk' => $item->id_produk,
                    'kategori' => $item->kategori,
                    'nama_produk' => $item->nama_produk,
                    'harga' => $minPrice, // Menyertakan harga terendah
                    'deskripsi' => $item->deskripsi,
                    'tanggal_dibuat' => $item->tanggal_dibuat,
                    'tanggal_diperbarui' => $item->tanggal_diperbarui,
                    'gambar_produk' => $item->gambarProduk // Menyertakan gambar produk
                    
                ];
            });
            return response()->json([
                'status' => 'success',
                'data' => $produk
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi Kesalahan, Tidak dapat mengambil  data produk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try{
            // Mengambil produk berdasarkan ID dengan relasi yang diperlukan
            $produk = Produk::with(['detailProduk','gambarProduk', 'produkVariasi'=> function ($query) {
                $query->where('status', 'aktif')
                    ->with(['detailProdukVariasi.opsiVariasi.tipeVariasi', 'gambarVariasi']);
                        }
                    ])->find($id);
            
            if (!$produk) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Produk tidak ditemukan'
                    ], 404);
                }

            return response()->json([
                'status' => 'success',
                'data' => $produk
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Terjadi Kesalahan, Tidak dapat mengambil  data produk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showVariation()
    {
        try{
            // Mengambil variasi produk berdasarkan ID variasi
            $variasi = TipeVariasi::with(['opsiVariasi'])
                        ->get();

            return response()->json([
                'status' => 'success',
                'data' => $variasi
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Terjadi Kesalahan, Tidak dapat mengambil Variasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {

        $requiredFields = ['id_kategori', 'nama_produk', 'gambar_produk', 'status'];
        $missingFields = array_diff($requiredFields, array_keys($request->all()));

        if (!empty($missingFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. Missing required fields: ' . implode(', ', $missingFields),
            ], 400); // 400 Bad Request
        }

        $kategori = Kategori::find($request->id_kategori);
        if (!$kategori) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not Found. Kategori dengan ID ' . $request->id_kategori . ' tidak ditemukan.',
            ], 404); // 404 Not Found
        }
    
        // Validasi request
        $validator = $this->validateProductRequest($request);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Buat produk utama
            $produk = $this->createMainProduct($request);

            // Tambah detail produk jika ada
            $this->addProductDetails($produk, $request);

            // Upload gambar produk
            $this->uploadProductImages($produk, $request);

            // Tentukan status default variasi
            $hasVariation = $this->hasProductVariation($request);

            // Buat variasi produk
            $this->createProductVariations($produk, $request, $hasVariation);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $produk->load([
                    'produkVariasi.detailProdukVariasi.opsiVariasi', 
                    'gambarProduk'
                ])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validasi komprehensif untuk request produk
     */
    private function validateProductRequest(Request $request)
    {
        $rules = [
            'id_kategori' => [ 
                'required'
            ],
            'nama_produk' => [ 
                'required', 
                'string', 
                'max:255'
            ],
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',

            // Validasi detail produk
            'detail_produk.deskripsi_detail' => 'nullable|string',
            'detail_produk.url_video' => 'nullable|url',
            
            // Validasi gambar produk
            'gambar_produk' => 'nullable|array',
            'gambar_produk.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            
            // Validasi variasi
            'variasi' => 'nullable|array',
            'variasi.*.stok' => 'required_with:variasi|integer|min:0',
            'variasi.*.berat' => 'required_with:variasi|numeric|min:0',
            'variasi.*.hpp' => 'required_with:variasi|numeric|min:0',
            'variasi.*.harga' => 'required_with:variasi|numeric|min:0',
            'variasi.*.status' => 'required|in:aktif,nonaktif',

            // Validasi tipe variasi
            'variasi.*.tipe_variasi' => 'array',
            'variasi.*.tipe_variasi.*.id_tipe_variasi' => 'sometimes|exists:tb_tipe_variasi,id_tipe_variasi',
            'variasi.*.tipe_variasi.*.nama_tipe' => 'required_without:variasi.*.tipe_variasi.*.id_tipe_variasi|string|max:255',
            
            // Validasi opsi variasi
            'variasi.*.tipe_variasi.*.opsi.id_opsi_variasi' => 'sometimes|exists:tb_opsi_variasi,id_opsi_variasi',
            'variasi.*.tipe_variasi.*.opsi.nama_opsi' => 'required_without:variasi.*.tipe_variasi.*.opsi.id_opsi_variasi|string|max:255',
            
            // Validasi gambar variasi (maks 1 gambar per variasi)
            'variasi.*.gambar' => 'nullable|array|max:1',
            'variasi.*.gambar.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ];

        return Validator::make($request->all(), $rules);
    }

    /**
     * Buat produk utama
     */
    private function createMainProduct(Request $request)
    {
        return Produk::create([
            'id_kategori' => $request->id_kategori,
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status
        ]);
    }

    /**
     * Tambah detail produk
     */
    private function addProductDetails(Produk $produk, Request $request)
    {
        if ($request->has('detail_produk')) {
            DetailProduk::create([
                'id_produk' => $produk->id_produk,
                'deskripsi_detail' => $request->detail_produk['deskripsi_detail'] ?? null,
                'url_video' => $request->detail_produk['url_video'] ?? null
            ]);
        }
    }

    /**
     * Upload gambar produk
     */
    private function uploadProductImages(Produk $produk, Request $request)
    {
        if ($request->hasFile('gambar_produk')) {
            foreach ($request->file('gambar_produk') as $gambar) {
                $uploadedFile = Cloudinary::upload($gambar->getRealPath(), [
                    'folder' => 'produk_images'
                ]);

                $cleanUrl = preg_replace('/\.[^.]+$/', '', $uploadedFile->getSecurePath());

                GambarProduk::create([
                    'id_produk' => $produk->id_produk,
                    'gambar' => $cleanUrl,
                    'public_id' => $uploadedFile->getPublicId()
                ]);
            }
        }
    }

    /**
     * Cek apakah produk memiliki variasi
     */
    private function hasProductVariation(Request $request): bool
    {
        return $request->has('variasi') && 
               !empty($request->variasi);
    }

    /**
     * Buat variasi produk
     */
    private function createProductVariations(Produk $produk, Request $request, bool $hasVariation)
    {
        // Jika tidak ada variasi, buat variasi default
        if (!$hasVariation) {
            ProdukVariasi::create([
                'id_produk' => $produk->id_produk,
                'stok' => $request->stok ?? 0,
                'berat' => $request->berat ?? 0,
                'hpp' => $request->hpp ?? 0,
                'harga' => $request->harga ?? 0,
                'status' => $request->status,
                'default' => 'benar'
            ]);
            return;
        }

        // Jika ada variasi, buat variasi sesuai input
        foreach ($request->variasi as $variasiItem) {
            // Buat variasi produk
            $produkVariasi = ProdukVariasi::create([
                'id_produk' => $produk->id_produk,
                'stok' => $variasiItem['stok'],
                'berat' => $variasiItem['berat'],
                'hpp' => $variasiItem['hpp'],
                'harga' => $variasiItem['harga'],
                'status' => $variasiItem['status'],
                'default' => 'salah'
            ]);

            // Proses tipe variasi dan opsi
            $this->processVariationTypes($produkVariasi, $variasiItem['tipe_variasi'] ?? []);

            // Upload gambar variasi
            $this->uploadVariationImage($produkVariasi, $variasiItem['gambar'] ?? null);
        }
    }
    /**
     * Proses tipe variasi dan opsi
     */
    private function processVariationTypes(ProdukVariasi $produkVariasi, array $tipeVariasiData)
    {
        foreach ($tipeVariasiData as $tipeVariasi) {
            // Cari atau buat tipe variasi
            $tipe = TipeVariasi::where('id_tipe_variasi', $tipeVariasi['id_tipe_variasi'] ?? null)
                ->orWhere('nama_tipe', $tipeVariasi['nama_tipe'] ?? null)
                ->first();

            // Jika tidak ada, buat tipe variasi baru
            if (!$tipe) {
                if (isset($tipeVariasi['nama_tipe'])) {
                    $tipe = TipeVariasi::create([
                        'nama_tipe' => $tipeVariasi['nama_tipe']
                    ]);
                } else {
                    // Tangani kasus di mana tidak ada nama_tipe
                    continue; // Atau bisa throw exception jika ini adalah kondisi yang tidak diinginkan
                }
            }

            // Cari atau buat opsi variasi
            $opsi = OpsiVariasi::where('id_opsi_variasi', $tipeVariasi['opsi']['id_opsi_variasi'] ?? null)
                ->orWhere(function($query) use ($tipe, $tipeVariasi) {
                    $query->where('id_tipe_variasi', $tipe->id_tipe_variasi)
                        ->where('nama_opsi', $tipeVariasi['opsi']['nama_opsi'] ?? null);
                })
                ->first();

            // Jika tidak ada, buat opsi variasi baru
            if (!$opsi) {
                if (isset($tipeVariasi['opsi']['nama_opsi'])) {
                    $opsi = OpsiVariasi::create([
                        'id_tipe_variasi' => $tipe->id_tipe_variasi,
                        'nama_opsi' => $tipeVariasi['opsi']['nama_opsi']
                    ]);
                } else {
                    // Tangani kasus di mana tidak ada nama_opsi
                    continue; // Atau bisa throw exception jika ini adalah kondisi yang tidak diinginkan
                }
            }

            // Simpan detail produk variasi
            DetailProdukVariasi::create([
                'id_produk_variasi' => $produkVariasi->id_produk_variasi,
                'id_opsi_variasi' => $opsi->id_opsi_variasi
            ]);
        }
    }

    /**
     * Upload gambar variasi
     */
    private function uploadVariationImage(ProdukVariasi $produkVariasi, ?array $gambarVariasi)
    {
        if ($gambarVariasi && count($gambarVariasi) > 0) {
            // Ambil gambar pertama (karena hanya boleh 1 gambar)
            $gambar = $gambarVariasi[0];

            // Upload ke Cloudinary
            $uploadedFile = Cloudinary::upload($gambar->getRealPath(), [
                'folder' => 'produk_variasi_images'
            ]);

            $cleanUrl = preg_replace('/\.[^.]+$/', '', $uploadedFile->getSecurePath());

            // Simpan gambar variasi
            GambarVariasi::create([
                'id_produk_variasi' => $produkVariasi->id_produk_variasi,
                'gambar' => $cleanUrl,
                'public_id' => $uploadedFile->getPublicId()
            ]);
        }
    }

    
    private function validateProductEditRequest(Request $request)
    {
        $rules = [
            // Validasi dasar produk
            'id_kategori' => [
                'sometimes', 
                'exists:tb_kategori,id_kategori'
            ],
            'nama_produk' => [
                'sometimes', 
                'string', 
                'max:255'
            ],
            'deskripsi' => 'nullable|string',
            // 'status' => 'sometimes|in:aktif,nonaktif',

            // Validasi detail produk
            'detail_produk.deskripsi_detail' => 'nullable|string',
            'detail_produk.url_video' => 'nullable|url',
            
            // Validasi gambar produk
            'gambar_produk' => 'nullable|array',
            'gambar_produk.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            
            // Validasi untuk variasi yang sudah ada (hanya bisa edit beberapa field)
            'variasi_existing' => 'nullable|array',
            'variasi_existing.*.id_produk_variasi' => 'required|exists:tb_produk_variasi,id_produk_variasi',
            'variasi_existing.*.stok' => 'required|integer|min:0',
            'variasi_existing.*.berat' => 'required|numeric|min:0',
            'variasi_existing.*.hpp' => 'required|numeric|min:0',
            'variasi_existing.*.harga' => 'required|numeric|min:0',
            'variasi_existing.*.status' => 'sometimes|in:aktif,nonaktif',
            'variasi_existing.*.gambar' => 'nullable|array|max:1',
            'variasi_existing.*.gambar.*' => 'image|mimes:jpeg,png,jpg|max:5120',

            // Validasi untuk variasi baru
            'variasi_baru' => 'nullable|array',
            'variasi_baru.*.tipe_variasi' => 'required|array|min:1',
            'variasi_baru.*.tipe_variasi.*.id_tipe_variasi' => [
                'required', 
                'exists:tb_tipe_variasi,id_tipe_variasi'
            ],
            'variasi_baru.*.tipe_variasi.*.opsi_variasi' => 'required|array|min:1',
            'variasi_baru.*.tipe_variasi.*.opsi_variasi.*.id_opsi_variasi' => 'nullable|exists:tb_opsi_variasi,id_opsi_variasi',
            'variasi_baru.*.tipe_variasi.*.opsi_variasi.*.nama_opsi' => 'nullable|string',
            
            
            // Validasi detail variasi baru
            'variasi_baru.*.stok' => 'required|integer|min:0',
            'variasi_baru.*.berat' => 'required|numeric|min:0',
            'variasi_baru.*.hpp' => 'required|numeric|min:0',
            'variasi_baru.*.harga' => 'required|numeric|min:0',
            'variasi_baru.*.status' => 'sometimes|in:aktif,nonaktif',
            
            // Validasi gambar variasi baru
            'variasi_baru.*.gambar' => 'nullable|array|max:1',
            'variasi_baru.*.gambar.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ];

        $messages = [
            // Pesan error kustom
            'variasi_existing.*.id_produk_variasi.exists' => 'Variasi produk tidak valid.',
            'variasi_baru.*.tipe_variasi.*.id_tipe_variasi.exists' => 'Tipe variasi tidak valid.',
            'variasi_baru.*.tipe_variasi.*.opsi_variasi.*.id_opsi_variasi.exists' => 'Opsi variasi tidak valid.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    // Contoh penggunaan dalam method update
    public function update(Request $request, $id)
    {
        if ($request->isNotFilled([
            'deskripsi',
            'detail_produk',
            'gambar_produk',
            'variasi_existing',
            'variasi_baru'
        ])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request. No data provided.',
            ], 400); // 400 Bad Request
        }

        // Ambil produk yang akan diupdate
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'status' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        if ($request->has('id_kategori')) {
            $kategori = Kategori::find($request->id_kategori);
            if (!$kategori) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not Found. Kategori dengan ID ' . $request->id_kategori . ' tidak ditemukan.',
                ], 404); // 404 Not Found
            }
        }

        $validator = $this->validateProductEditRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update informasi dasar produk
            $updateData = [];
            if ($request->has('id_kategori')) {
                $updateData['id_kategori'] = $request->id_kategori;
            }
            if ($request->has('nama_produk')) {
                $updateData['nama_produk'] = $request->nama_produk;
            }
            if ($request->has('deskripsi')) {
                $updateData['deskripsi'] = $request->deskripsi;
            }

            if (!empty($updateData)) {
                $produk->update($updateData);
            }

            // Update detail produk
            $this->updateProductDetails($produk, $request);

            // Update gambar produk
            $this->updateProductImages($produk, $request);

            // Update variasi yang sudah ada
            $this->updateExistingVariations($produk, $request->variasi_existing);

            // Tambah variasi baru
            $this->addNewVariations($produk, $request->variasi_baru);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $produk->load([
                    'produkVariasi.detailProdukVariasi.opsiVariasi.tipeVariasi',
                    'gambarProduk',
                    'detailProduk'
                ])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }


    private function updateProductDetails(Produk $produk, Request $request)
    {
        if ($request->has('detail_produk')) {
            DetailProduk::updateOrCreate(
                ['id_produk' => $produk->id_produk],
                [
                    'deskripsi_detail' => $request->input('detail_produk.deskripsi_detail'),
                    'url_video' => $request->input('detail_produk.url_video')
                ]
            );
        }
    }

    private function updateProductImages(Produk $produk, Request $request)
    {
        // Hapus gambar lama jika ada gambar baru
        if ($request->hasFile('gambar_produk')) {
            // Hapus gambar lama dari Cloudinary
            foreach ($produk->gambarProduk as $gambarLama) {
                if ($gambarLama->public_id) {
                    Cloudinary::destroy($gambarLama->public_id);
                }
            }
            
            // Hapus record gambar lama dari database
            $produk->gambarProduk()->delete();

            // Upload gambar baru
            $this->uploadProductImages($produk, $request->file('gambar_produk'));
        }
    }

    private function updateExistingVariations(Produk $produk, $existingVariations)
    {
        if (!$existingVariations) return;

        foreach ($existingVariations as $variasiData) {
            // Temukan variasi yang akan diupdate
            $variasi = ProdukVariasi::findOrFail($variasiData['id_produk_variasi']);
            
            // Update detail variasi
            $variasi->update([
                'stok' => $variasiData['stok'],
                'berat' => $variasiData['berat'],
                'hpp' => $variasiData['hpp'],
                'harga' => $variasiData['harga'],
                'status' => $variasiData['status']
            ]);

            // Proses gambar variasi jika ada
            $this->updateVariationImages($variasi, $variasiData['gambar'] ?? null);
        }
    }

    private function updateVariationImages(ProdukVariasi $variasi, $images)
    {
        if (!$images) return;

        // Hapus gambar variasi lama
        foreach ($variasi->gambarVariasi as $gambarLama) {
            if ($gambarLama->public_id) {
                Cloudinary::destroy($gambarLama->public_id);
            }
        }
        $variasi->gambarVariasi()->delete();

        // Upload gambar baru
        $this->uploadVariationImage($variasi, $images);
    }

    private function addNewVariations(Produk $produk, $newVariations)
    {
        if (!$newVariations) return;

        foreach ($newVariations as $variasiData) {
            // Buat variasi baru
            $variasi = $produk->variasi()->create([
                'stok' => $variasiData['stok'],
                'berat' => $variasiData['berat'],
                'hpp' => $variasiData['hpp'],
                'harga' => $variasiData['harga'],
                'status' => $variasiData['status'],
                'default' => 'salah'
            ]);

            // Proses tipe variasi dan opsi
            $this->processNewVariationTypes($variasi, $variasiData['tipe_variasi']);

            // Proses gambar variasi
            if (isset($variasiData['gambar']) && $variasiData['gambar']) {
                $this->uploadVariationImage($variasi, $variasiData['gambar']);
            }
        }
    }

    private function processNewVariationTypes(ProdukVariasi $variasi, $tipeVariasiData)
    {
        foreach ($tipeVariasiData as $tipeVariasi) {
            // Pastikan tipe variasi sudah ada di database (dari API atau tersimpan)
            $tipe = TipeVariasi::findOrFail($tipeVariasi['id_tipe_variasi']);

            // Proses opsi variasi
            foreach ($tipeVariasi['opsi_variasi'] as $opsiData) {
                // Jika diberikan id_opsi_variasi, gunakan opsi yang sudah ada
                if (isset($opsiData['id_opsi_variasi'])) {
                    $opsi = OpsiVariasi::where('id_tipe_variasi', $tipe->id_tipe_variasi)
                        ->findOrFail($opsiData['id_opsi_variasi']);
                } 
                // Jika diberikan nama, buat opsi baru
                elseif (isset($opsiData['nama_opsi'])) {
                    $opsi = OpsiVariasi::create([
                        'id_tipe_variasi' => $tipe->id_tipe_variasi,
                        'nama_opsi' => $opsiData['nama_opsi']
                    ]);
                } 
                // Jika tidak ada id atau nama, lempar exception
                else {
                    throw new \Exception('Opsi variasi tidak valid');
                }

                // Simpan detail produk variasi
                DetailProdukVariasi::create([
                    'id_produk_variasi' => $variasi->id_produk_variasi,
                    'id_opsi_variasi' => $opsi->id_opsi_variasi
                ]);
            }
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // Find the product
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json([
                'status' => false,
                'message' => 'Produk Tidak Ditemukan'
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
        // Validate input
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Start database transaction
        DB::beginTransaction();
        try {

            // Update product status
            $produk->status = $request->status;
            $produk->save();

            // If product is being deactivated, deactivate all its variations
            if ($request->status === 'nonaktif') {
                ProdukVariasi::where('id_produk', $produk->id_produk)
                ->update([
                    'status' => 'nonaktif'
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status produk berhasil diperbarui',
                'data' => $produk
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui status produk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateVariationStatus(Request $request, $variationId)
    {
        $produkVariasi = ProdukVariasi::find($variationId);
        if (!$produkVariasi) {
            return response()->json([
                'status' => false,
                'message' => 'Variasi Tidak Ditemukan'
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
        // Validate input
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find the product variation
            

            // Update variation status
            $produkVariasi->status = $request->status;
            $produkVariasi->save();

            // Check if all variations are non-active
            $allVariationsInactive = ProdukVariasi::where('id_produk', $produkVariasi->id_produk)
                ->where('status', 'aktif')
                ->count() === 0;

            // If all variations are inactive, deactivate the main product
            if ($allVariationsInactive) {
                $produk = Produk::findOrFail($produkVariasi->id_produk);
                $produk->status = 'nonaktif';
                $produk->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Status variasi produk berhasil diperbarui',
                'data' => $produkVariasi
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui status variasi produk: ' . $e->getMessage()
            ], 500);
        }
    }

}
