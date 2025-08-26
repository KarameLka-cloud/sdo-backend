<?php

namespace App\Http\Controllers\Education;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationTest;
use Illuminate\Http\Request;

class EducationTestController extends Controller
{
    public function index(): JsonResponse
    {
        $tests = EducationTest::orderBy('date_end', 'desc')->get();
        return response()->json($tests);
    }

    public function store(Request $request): JsonResponse
    {
        $test = EducationTest::create($request->only(['title', 'url', 'date_end']));
        return response()->json($test);
    }

    public function show($id): JsonResponse
    {
        $test = EducationTest::findOrFail($id);
        return response()->json($test);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $test = EducationTest::findOrFail($id);
        $test->update($request->only(['title', 'url', 'date_end']));
        return response()->json($test);
    }

    public function destroy($id): JsonResponse
    {
        $test = EducationTest::findOrFail($id);
        $test->delete();
        return response()->json(['message' => 'Test deleted']);
    }
}
