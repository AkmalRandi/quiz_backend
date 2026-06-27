<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run()
    {
        Guru::create([
            'nama_guru' => 'Dr. Ahmad Fauzi, M.Pd.',
            'username' => 'ahmadfauzi',
            'password' => Hash::make('gurupintar'),
        ]);

        Guru::create([
            'nama_guru' => 'Siti Rahayu, S.Si.',
            'username' => 'sitirahayu',
            'password' => Hash::make('guruhebat'),
        ]);

        Guru::create([
            'nama_guru' => 'Bambang Susilo, M.Kom.',
            'username' => 'bambang',
            'password' => Hash::make('gurupro'),
        ]);
    }
}