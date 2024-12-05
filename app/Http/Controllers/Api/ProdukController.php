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
use App\Models\Kategori1;
use App\Models\Kategori2;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil semua produk tanpa relasi produk variasi
        $produk = Produk::with('kategori2','gambarProduk')->get();

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
                'kategori_2' => $item->kategori2,
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
    }

    public function show($id)
    {
        // Mengambil produk berdasarkan ID dengan relasi yang diperlukan
        $produk = Produk::with(['detailProduk','gambarProduk', 'produkVariasi.detailProdukVariasi.opsiVariasi.tipeVariasi', 'produkVariasi.gambarVariasi'])
                        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $produk
        ], 200);
    }

    public function showVariation($id_variation)
    {
        // Mengambil variasi produk berdasarkan ID variasi
        $produkVariasi = ProdukVariasi::with(['detailProdukVariasi.opsiVariasi'])
                                    ->findOrFail($id_variation);

        return response()->json([
            'status' => 'success',
            'data' => $produkVariasi
        ], 200);
    }

    public function store(Request $request)
    {
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
            // Validasi produk dasar
            // 'id_kategori_2' => 'required|exists:tb_kategori_2,id_kategori_2',
            // 'nama_produk' => 'required|string|max:255',
            'id_kategori_2' => [
                'sometimes', 
                'required', 
                'exists:tb_kategori_2,id_kategori_2'
            ],
            'nama_produk' => [
                'sometimes', 
                'required', 
                'string', 
                'max:255'
            ],
            
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
            'id_kategori_2' => $request->id_kategori_2,
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi
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

                GambarProduk::create([
                    'id_produk' => $produk->id_produk,
                    'gambar' => $uploadedFile->getSecurePath(),
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

            // Simpan gambar variasi
            GambarVariasi::create([
                'id_produk_variasi' => $produkVariasi->id_produk_variasi,
                'gambar' => $uploadedFile->getSecurePath(),
                'public_id' => $uploadedFile->getPublicId()
            ]);
        }
    }

    public function updated(Request $request, $id)
    {
        \Log::info('Data yang akan diupdate', $request->all());
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
            // Mengambil produk berdasarkan ID
            $produk = Produk::findOrFail($id);
            
            // Update produk utama
            $produk->update([
                'id_kategori_2' => $request->id_kategori_2,
                'nama_produk' => $request->nama_produk,
                'deskripsi' => $request->deskripsi
            ]);

            // Update detail produk
            $this->updateProductDetails($produk, $request);

            // Proses gambar produk
            $this->updateProductImages($produk, $request);

            // Proses variasi produk
            $this->updateProductVariations($produk, $request);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $produk->load([
                    'produkVariasi.detailProdukVariasi.opsiVariasi', 
                    'gambarProduk'
                ])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update detail produk
     */
    private function updateProductDetails(Produk $produk, Request $request)
    {
        if ($request->has('detail_produk')) {
            DetailProduk::updateOrCreate(
                ['id_produk' => $produk->id_produk],
                [
                    'deskripsi_detail' => $request->detail_produk['deskripsi_detail'] ?? null,
                    'url_video' => $request->detail_produk['url_video'] ?? null
                ]
            );
        }
    }

    /**
     * Update gambar produk
     */
    private function updateProductImages(Produk $produk, Request $request)
    {
        // Jika ada gambar baru di upload
        if ($request->hasFile('gambar_produk')) {

            foreach ($produk->gambarProduk as $gambar) {
                Cloudinary::destroy($gambar->public_id);
            }
            // Hapus gambar lama
            $produk->gambarProduk()->delete();

            // Upload gambar baru
            $this->uploadProductImages($produk, $request);
        }
    }

    /**
     * Update variasi produk
     */
    private function updateProductVariations(Produk $produk, Request $request)
    {
        // Cek apakah ada variasi di request
        $hasVariation = $this->hasProductVariation($request);

        if ($hasVariation) {
            // Ambil variasi yang ada
            $existingVariations = $produk->produkVariasi;

            // Buat array untuk menyimpan ID variasi yang sudah ada
            $existingVariationIds = $existingVariations->pluck('id_produk_variasi')->toArray();

            // Iterasi melalui variasi yang dikirimkan
            foreach ($request->variasi as $variasiItem) {
                // Cek apakah variasi sudah ada
                if (isset($variasiItem['id_produk_variasi']) && in_array($variasiItem['id_produk_variasi'], $existingVariationIds)) {
                    // Update variasi yang ada
                    $existingVariation = $existingVariations->where('id_produk_variasi', $variasiItem['id_produk_variasi'])->first();
                    $existingVariation->update([
                        'stok' => $variasiItem['stok'],
                        'berat' => $variasiItem['berat'],
                        'hpp' => $variasiItem['hpp'],
                        'harga' => $variasiItem['harga'],
                    ]);

                    // Proses tipe variasi dan opsi
                    $this->processVariationTypes($existingVariation, $variasiItem['tipe_variasi'] ?? []);

                    // Jika ada gambar baru, upload gambar variasi
                    if (isset($variasiItem['gambar']) && count($variasiItem['gambar']) > 0) {
                        // Hapus gambar lama jika ada
                        foreach ($existingVariation->gambarVariasi as $gambarVariasi) {
                            Cloudinary::destroy($gambarVariasi->public_id);
                        }
                        // Hapus gambar variasi lama
                        $existingVariation->gambarVariasi()->delete();

                        // Upload gambar variasi baru
                        $this->uploadVariationImage($existingVariation, $variasiItem['gambar']);
                    }
                } else {
                    // Jika variasi baru, buat variasi baru
                    $produkVariasi = ProdukVariasi::create([
                        'id_produk' => $produk->id_produk,
                        'stok' => $variasiItem['stok'],
                        'berat' => $variasiItem['berat'],
                        'hpp' => $variasiItem['hpp'],
                        'harga' => $variasiItem['harga'],
                        'default' => 'salah'
                    ]);

                    // Proses tipe variasi dan opsi
                    $this->processVariationTypes($produkVariasi, $variasiItem['tipe_variasi'] ?? []);

                    // Upload gambar variasi jika ada
                    if (isset($variasiItem['gambar']) && count($variasiItem['gambar']) > 0) {
                        $this->uploadVariationImage($produkVariasi, $variasiItem['gambar']);
                    }
                }
            }
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Mengambil produk berdasarkan ID
            $produk = Produk::findOrFail($id);

            // Hapus gambar produk
            foreach ($produk->gambarProduk as $gambar) {
                Cloudinary::destroy($gambar->public_id);
            }

            // Hapus variasi produk
            foreach ($produk->produkVariasi as $variasi) {
                // Hapus gambar variasi
                foreach ($variasi->gambarVariasi as $gambarVariasi) {
                    Cloudinary::destroy($gambarVariasi->public_id);
                }
                // Hapus variasi
                $variasi->delete();
            }

            // Hapus detail produk
            $produk->detailProduk()->delete();

            // Hapus produk utama
            $produk->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Produk berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
