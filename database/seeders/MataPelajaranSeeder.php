<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataPelajaran;

class MataPelajaranSeeder extends Seeder
{
    public function run()
    {
        $data = ['Matematika', 'Fisika', 'Kimia'];
        foreach ($data as $nama) {
            MataPelajaran::create(['nama_mapel' => $nama]);
        }
    }
}