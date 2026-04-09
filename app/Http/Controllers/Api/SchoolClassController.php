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
}