<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['user_id', 'school_class_id', 'school_id', 'name', 'grade', 'reading_level'];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function readingSessions()
    {
        return $this->hasMany(ReadingSession::class);
    }
}