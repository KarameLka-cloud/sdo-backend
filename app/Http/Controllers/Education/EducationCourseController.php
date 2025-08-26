<?php

namespace App\Http\Controllers\Education;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationCourse;
use Illuminate\Http\Request;

class EducationCourseController extends Controller
{
    public function index(): JsonResponse
    {
        $courses = EducationCourse::orderBy('date_end', 'desc')->get();
        return response()->json($courses);
    }

    public function store(Request $request): JsonResponse
    {
        $course = EducationCourse::create($request->only(['title', 'url', 'date_end']));
        return response()->json($course);
    }

    public function show($id): JsonResponse
    {
        $course = EducationCourse::findOrFail($id);
        return response()->json($course);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $course = EducationCourse::findOrFail($id);
        $course->update($request->only(['title', 'url', 'date_end']));
        return response()->json($course);
    }

    public function destroy($id): JsonResponse
    {
        $course = EducationCourse::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
