<?php

namespace App\Http\Controllers\Edo;

use App\Http\Requests\CourseRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Edo\EdoCourse;

class EdoCourseController extends Controller
{
    public function index(): JsonResponse
    {
        $courses = EdoCourse::orderBy('date', 'desc')->get();
        return response()->json($courses);
    }

    public function store(CourseRequest $request): JsonResponse
    {
        $course = EdoCourse::create($request->validated());
        return response()->json($course);
    }

    public function show($id): JsonResponse
    {
        $course = EdoCourse::findOrFail($id);
        return response()->json($course);
    }

    public function update(CourseRequest $request, $id): JsonResponse
    {
        $course = EdoCourse::findOrFail($id);
        $course->update($request->validated());
        return response()->json($course);
    }

    public function destroy($id): JsonResponse
    {
        $course = EdoCourse::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
