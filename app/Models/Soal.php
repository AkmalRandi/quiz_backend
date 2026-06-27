<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soal';
    
    protected $fillable = [
        'mapel_id',
        'pertanyaan',
        'gambar',
        'jawaban_benar'
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function opsi()
    {
        return $this->hasMany(Opsi::class, 'soal_id');
    }
}