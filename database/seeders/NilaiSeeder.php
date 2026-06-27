<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nilai;

class NilaiSeeder extends Seeder
{
    public function run()
    {
        // Siswa ID 1 (Budi Santoso)
        Nilai::create([
            'id_siswa' => 1,
            'id_mapel' => 1, // Matematika
            'skor' => 85,
        ]);
        Nilai::create([
            'id_siswa' => 1,
            'id_mapel' => 2, // Fisika
            'skor' => 90,
        ]);
        Nilai::create([
            'id_siswa' => 1,
            'id_mapel' => 3, // Kimia
            'skor' => 75,
        ]);

        // Siswa ID 2 (Ani Wijaya)
        Nilai::create([
            'id_siswa' => 2,
            'id_mapel' => 1,
            'skor' => 95,
        ]);
        Nilai::create([
            'id_siswa' => 2,
            'id_mapel' => 4, // Biologi
            'skor' => 88,
        ]);

        // Siswa ID 3 (Citra Dewi)
        Nilai::create([
            'id_siswa' => 3,
            'id_mapel' => 5, // Sejarah
            'skor' => 78,
        ]);
        Nilai::create([
            'id_siswa' => 3,
            'id_mapel' => 6, // Bahasa Indonesia
            'skor' => 82,
        ]);
    }
}