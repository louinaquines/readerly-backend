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

        $class = SchoolClass::create([
            'teacher_id'  => auth('api')->id(),
            'name'        => $request->name,
            'grade_level' => $request->grade_level,
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
            'student_id' => 'required|string', // Expecting "STU-0007"
            'school_class_id' => 'required|exists:school_classes,id'
        ]);

        // Find the user who has that specific ID
        $user = \App\Models\User::where('student_id', $request->student_id)->first();

        if (!$user) {
            return response()->json(['message' => 'ID not found.'], 404);
        }

        // Link their student profile to the class
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        
        if ($student) {
            $student->update(['school_class_id' => $request->school_class_id]);
            return response()->json(['message' => 'Student enrolled successfully!']);
        }

        return response()->json(['message' => 'Student profile missing.'], 404);
    }
    public function enrollByStudentId(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string', // This will take "STU-0007"
            'school_class_id' => 'required|exists:school_classes,id'
        ]);

        // Find the student by that specific ID string
        $student = \App\Models\User::where('student_id', $request->student_id)
                                ->where('role', 'student')
                                ->first();

        if (!$student) {
            return response()->json(['message' => 'Student ID not found.'], 404);
        }

        // Now link this user to the class. 
        // Since your 'students' table has a 'user_id', we update the Student record.
        $studentProfile = \App\Models\Student::where('user_id', $student->id)->first();
        
        if ($studentProfile) {
            $studentProfile->update(['school_class_id' => $request->school_class_id]);
            return response()->json(['message' => "Successfully enrolled {$student->name}!"]);
        }

        return response()->json(['message' => 'Student profile record missing.'], 404);
    }
}