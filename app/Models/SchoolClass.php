<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SchoolClass extends Model
{
    protected $table = 'school_classes';
    protected $fillable = ['teacher_id', 'name', 'grade_level', 'class_code'];
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}