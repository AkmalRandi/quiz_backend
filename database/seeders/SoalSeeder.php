<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Soal;

class SoalSeeder extends Seeder
{
    public function run()
    {
        // Mata Pelajaran ID: 1 = Matematika
        Soal::create([
            'id_mapel' => 1,
            'pertanyaan' => 'Berapakah hasil dari 2 + 2?',
            'opsi_a' => '2',
            'opsi_b' => '3',
            'opsi_c' => '4',
            'opsi_d' => '5',
            'jawaban_benar' => 'c',
            'gambar' => null,
        ]);

        Soal::create([
            'id_mapel' => 1,
            'pertanyaan' => 'Akar kuadrat dari 25 adalah?',
            'opsi_a' => '4',
            'opsi_b' => '5',
            'opsi_c' => '6',
            'opsi_d' => '7',
            'jawaban_benar' => 'b',
            'gambar' => null,
        ]);

        // Fisika (id_mapel = 2)
        Soal::create([
            'id_mapel' => 2,
            'pertanyaan' => 'Satuan SI untuk gaya adalah?',
            'opsi_a' => 'Newton',
            'opsi_b' => 'Joule',
            'opsi_c' => 'Watt',
            'opsi_d' => 'Pascal',
            'jawaban_benar' => 'a',
            'gambar' => null,
        ]);

        Soal::create([
            'id_mapel' => 2,
            'pertanyaan' => 'Rumus energi kinetik adalah?',
            'opsi_a' => '1/2 mv²',
            'opsi_b' => 'mgh',
            'opsi_c' => 'Fs',
            'opsi_d' => 'Pt',
            'jawaban_benar' => 'a',
            'gambar' => null,
        ]);

        // Kimia (id_mapel = 3)
        Soal::create([
            'id_mapel' => 3,
            'pertanyaan' => 'Lambang kimia dari Air adalah?',
            'opsi_a' => 'H2O',
            'opsi_b' => 'CO2',
            'opsi_c' => 'NaCl',
            'opsi_d' => 'HCl',
            'jawaban_benar' => 'a',
            'gambar' => null,
        ]);

        // Biologi (id_mapel = 4)
        Soal::create([
            'id_mapel' => 4,
            'pertanyaan' => 'Organela yang berfungsi sebagai pusat respirasi sel adalah?',
            'opsi_a' => 'Mitokondria',
            'opsi_b' => 'Ribosom',
            'opsi_c' => 'Nukleus',
            'opsi_d' => 'Vakuola',
            'jawaban_benar' => 'a',
            'gambar' => null,
        ]);

        // Sejarah (id_mapel = 5)
        Soal::create([
            'id_mapel' => 5,
            'pertanyaan' => 'Proklamasi Kemerdekaan Indonesia terjadi pada tahun?',
            'opsi_a' => '1942',
            'opsi_b' => '1945',
            'opsi_c' => '1946',
            'opsi_d' => '1949',
            'jawaban_benar' => 'b',
            'gambar' => null,
        ]);
    }
}