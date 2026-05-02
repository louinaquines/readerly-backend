<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::where('teacher_id', auth('api')->id())
            ->withCount('students')
            ->get();

        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'grade_level' => 'required|string',
        ]);

        // Generate a unique 6-character alphanumeric code
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        } while (\App\Models\SchoolClass::where('class_code', $code)->exists());

        $class = SchoolClass::create([
            'teacher_id'  => auth('api')->id(),
            'name'        => $request->name,
            'grade_level' => $request->grade_level,
            'class_code'  => $code,
        ]);

        return response()->json($class, 201);
    }

    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->load('students');
    
        return response()->json([
            'id'          => $schoolClass->id,
            'teacher_id'  => $schoolClass->teacher_id,
            'name'        => $schoolClass->name,
            'grade_level' => $schoolClass->grade_level,
            'created_at'  => $schoolClass->created_at,
            'updated_at'  => $schoolClass->updated_at,
            'students'    => $schoolClass->students,
        ]);
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        $schoolClass->update($request->only('name', 'grade_level'));
        return response()->json($schoolClass);
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        return response()->json(['message' => 'Class deleted']);
    }

    
    public function enrollStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'class_code' => 'required|string|exists:school_classes,class_code',
        ]);

        $class = \App\Models\SchoolClass::where('class_code', $request->class_code)->first();

        $user = \App\Models\User::where('student_id', $request->student_id)
                                ->where('role', 'student')
                                ->first();

        if (!$user) {
            return response()->json(['message' => 'Student ID not found.'], 404);
        }

        $studentProfile = \App\Models\Student::where('user_id', $user->id)->first();

        if (!$studentProfile) {
            return response()->json(['message' => 'Student profile is missing.'], 404);
        }

        $studentProfile->update(['school_class_id' => $class->id]);

        return response()->json([
            'message' => "Successfully enrolled {$user->name} in {$class->name}!",
            'student' => $studentProfile,
            'class'   => $class,
        ], 200);
    }

    public function enrollByStudentId(Request $request)
    {
        return $this->enrollStudent($request);
    }

    
}