<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'full_name' => 'Guru Utama',
            'username' => 'guru123',
            'email' => 'guru@example.com',
            'password' => Hash::make('guru123'),
            'role' => 'guru'
        ]);
        User::create([
            'full_name' => 'Budi Santoso',
            'username' => 'budi123',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'kelas' => '12 IPA 1'
        ]);
    }
}