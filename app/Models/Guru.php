<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Guru extends Model implements JWTSubject
{
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    protected $fillable = ['nama_guru', 'username', 'password'];
    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}