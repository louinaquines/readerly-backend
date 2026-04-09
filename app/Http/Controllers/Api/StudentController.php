<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(SchoolClass $schoolClass)
    {
        return response()->json($schoolClass->students);
    }

    public function store(Request $request, SchoolClass $schoolClass)
    {
        $request->validate([
            'name'  => 'required|string',
            'grade' => 'required|string',
        ]);

        $student = $schoolClass->students()->create([
            'name'          => $request->name,
            'grade'         => $request->grade,
            'reading_level' => 1,
        ]);

        return response()->json($student, 201);
    }

    public function show(SchoolClass $schoolClass, Student $student)
    {
        return response()->json($student->load('readingSessions'));
    }

    public function update(Request $request, SchoolClass $schoolClass, Student $student)
    {
        $student->update($request->only('name', 'grade', 'reading_level'));
        return response()->json($student);
    }

    public function destroy(SchoolClass $schoolClass, Student $student)
    {
        $student->delete();
        return response()->json(['message' => 'Student deleted']);
    }
}