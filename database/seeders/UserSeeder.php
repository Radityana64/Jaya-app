<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // User::create([
        //     'nama_lengkap' => 'Radityana',
        //     'email' => 'radityana64@gmail.com',
        //     'password' => '123123', // Password akan di-hash di sini
        //     'role' => 'admin',
        // ]);

        // User::create([
        //     'nama_lengkap' => 'Adi',
        //     'email' => 'adiw12@gmail.com',
        //     'password' => '123123', // Password akan di-hash di sini
        //     'role' => 'admin',
        // ]);

        User::create([
            'nama_lengkap' => 'Jaya Studio',
            'email' => 'jaya@gmail.com',
            'password' => 'JayaBangli15.', // Password di-hash
            'role' => 'pemilik_toko',
        ]);
    }
}

