<?php

namespace App\Http\Controllers\Education;

use App\Http\Requests\CourseRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationCourse;

class EducationCourseController extends Controller
{
    public function index(): JsonResponse
    {
        $courses = EducationCourse::orderBy('date', 'desc')->get();
        return response()->json($courses);
    }

    public function store(CourseRequest $request): JsonResponse
    {
        $course = EducationCourse::create($request->validated());
        return response()->json($course);
    }

    public function show($id): JsonResponse
    {
        $course = EducationCourse::findOrFail($id);
        return response()->json($course);
    }

    public function update(CourseRequest $request, $id): JsonResponse
    {
        $course = EducationCourse::findOrFail($id);
        $course->update($request->validated());
        return response()->json($course);
    }

    public function destroy($id): JsonResponse
    {
        $course = EducationCourse::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
