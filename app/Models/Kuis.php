<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $fillable = [
        'id_mapel',
        'judul',
        'deskripsi',
        'durasi'
    ];

    // Relasi ke mata pelajaran
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel');
    }

    // Relasi ke soal (satu kuis punya banyak soal)
    public function soal()
    {
        return $this->hasMany(Soal::class, 'id_kuis');
    }

    // Relasi ke nilai (satu kuis diikuti banyak siswa)
    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'id_kuis');
    }
}