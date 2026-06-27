<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $table = 'quiz_results';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'score',
        'correct_answers',
        'total_questions',
        'answers'
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'integer',
        'correct_answers' => 'integer',
        'total_questions' => 'integer'
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}