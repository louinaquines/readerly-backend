<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedStory extends Model
{
    protected $fillable = [
        'student_id',
        'reading_session_id',
        'story',
        'target_patterns',
    ];

    protected $casts = [
        'target_patterns' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function readingSession()
    {
        return $this->belongsTo(ReadingSession::class);
    }
}