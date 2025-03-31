<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ProfilViewController extends Controller
{
    public function index()
    {
        // $user = Auth::user();
        return view('pelanggan.app');
    }

    public function loadPage($page)
    {
        $validPages = ['profil', 'alamat', 'voucher', 'pesanan'];
        abort_if(!in_array($page, $validPages), 404);

        if (request()->ajax()) {
            return view("pelanggan.{$page}"); // Return the specific page view
        }

        return view('pelanggan.app', [
            'activePage' => $page // Set the active page for the sidebar
        ]);
    }
}
