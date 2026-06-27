<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';

    protected $fillable = [
        'question_id',
        'option_text',
        'option_image',
        'option_index'
    ];

    protected $casts = [
        'option_index' => 'integer'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}