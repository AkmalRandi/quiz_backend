<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kuis;

class KuisSeeder extends Seeder
{
    public function run()
    {
        Kuis::create([
            'id_mapel' => 1, // pastikan ID mata pelajaran ada
            'judul' => 'Kuis Matematika Dasar',
            'deskripsi' => 'Soal-soal dasar matematika untuk siswa kelas 10',
            'durasi' => 30
        ]);

        Kuis::create([
            'id_mapel' => 2,
            'judul' => 'Fisika Mekanika',
            'deskripsi' => 'Hukum Newton dan gerak lurus',
            'durasi' => 40
        ]);

        Kuis::create([
            'id_mapel' => 3,
            'judul' => 'Kimia Unsur',
            'deskripsi' => 'Tabel periodik dan ikatan kimia',
            'durasi' => 25
        ]);
    }
}