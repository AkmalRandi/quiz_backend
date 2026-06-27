<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    
    protected $fillable = [
        'nama',
        'deskripsi',
        'guru_id'
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function soal()
    {
        return $this->hasMany(Soal::class, 'mapel_id');
    }
}