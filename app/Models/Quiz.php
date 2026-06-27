<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $fillable = [
        'teacher_id',
        'title',
        'subject',
        'cover_image',
        'visibility',
        'join_code',
        'total_time',
        'total_points',
        'description'
    ];

    protected $casts = [
        'total_time' => 'integer',
        'total_points' => 'integer'
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }
}