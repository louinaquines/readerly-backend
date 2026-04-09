<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingSession extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'passage',
        'transcript',
        'accuracy_score',
        'error_patterns',
        'status',
    ];

    protected $casts = [
        'error_patterns' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}

class ReadingLevel extends Model
{
    protected $fillable = ['level', 'accuracy_threshold'];
}