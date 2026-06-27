<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opsi extends Model
{
    protected $table = 'opsi';
    
    protected $fillable = [
        'soal_id',
        'teks',
        'gambar',
        'is_benar'
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}