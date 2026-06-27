<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        Siswa::create([
            'nama_siswa' => 'Budi Santoso',
            'kelas' => '12 IPA 1',
            'username' => 'budi123',
            'password' => Hash::make('password123'),
        ]);

        Siswa::create([
            'nama_siswa' => 'Ani Wijaya',
            'kelas' => '12 IPA 2',
            'username' => 'ani456',
            'password' => Hash::make('password456'),
        ]);

        Siswa::create([
            'nama_siswa' => 'Citra Dewi',
            'kelas' => '12 IPS 1',
            'username' => 'citra789',
            'password' => Hash::make('password789'),
        ]);
    }
}