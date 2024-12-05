<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori1;
use App\Models\Kategori2;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // Get all Kategori1 with their Kategori2
    public function index()
    {
        $kategori1 = Kategori1::with('kategori2')->get();

        return response()->json([
            'status' => 'success',
            'data' => $kategori1
        ], 200);
    }

    // Create a new Kategori1
    public function storeKategori1(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori1 = Kategori1::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $kategori1
        ], 201);
    }

    // Update Kategori1
    public function updateKategori1(Request $request, $id)
    {
        $kategori1 = Kategori1::findOrFail($id);
        $kategori1->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $kategori1
        ], 200);
    }

    // Delete Kategori1
    public function destroyKategori1($id)
    {
        $kategori1 = Kategori1::findOrFail($id);
        $kategori1->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori1 deleted successfully'
        ], 200);
    }

    // Create a new Kategori2
    public function storeKategori2(Request $request)
    {
        $request->validate([
            'id_kategori_1' => 'required|exists:tb_kategori_1,id_kategori_1',
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori2 = Kategori2::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $kategori2
        ], 201);
    }

    // Update Kategori2
    public function updateKategori2(Request $request, $id)
    {
        $kategori2 = Kategori2::findOrFail($id);
        $kategori2->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $kategori2
        ], 200);
    }

    // Delete Kategori2
    public function destroyKategori2($id)
    {
        $kategori2 = Kategori2::findOrFail($id);
        $kategori2->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori2 deleted successfully'
        ], 200);
    }
}