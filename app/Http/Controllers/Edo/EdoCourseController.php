<?php

namespace App\Http\Controllers\Edo;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Edo\EdoCourse;
use Illuminate\Http\Request;

class EdoCourseController extends Controller
{
    public function index(): JsonResponse
    {
        $courses = EdoCourse::orderBy('date_end')->get();
        return response()->json($courses);
    }

    public function store(Request $request): JsonResponse
    {
        $course = EdoCourse::create($request->only(['title', 'date_end']));
        return response()->json($course);
    }

    public function show($id): JsonResponse
    {
        $course = EdoCourse::findOrFail($id);
        return response()->json($course);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $course = EdoCourse::findOrFail($id);
        $course->update($request->only(['title', 'date_end']));
        return response()->json($course);
    }

    public function destroy($id): JsonResponse
    {
        $course = EdoCourse::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
