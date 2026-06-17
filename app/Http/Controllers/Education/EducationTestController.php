<?php

namespace App\Http\Controllers\Education;

use App\Http\Requests\TestRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Education\EducationTest;

class EducationTestController extends Controller
{
    public function index(): JsonResponse
    {
        $tests = EducationTest::orderBy('date', 'desc')->get();
        return response()->json($tests);
    }

    public function store(TestRequest $request): JsonResponse
    {
        $test = EducationTest::create($request->validated());
        return response()->json($test);
    }

    public function show($id): JsonResponse
    {
        $test = EducationTest::findOrFail($id);
        return response()->json($test);
    }

    public function update(TestRequest $request, $id): JsonResponse
    {
        $test = EducationTest::findOrFail($id);
        $test->update($request->validated());
        return response()->json($test);
    }

    public function destroy($id): JsonResponse
    {
        $test = EducationTest::findOrFail($id);
        $test->delete();
        return response()->json(['message' => 'Test deleted']);
    }
}
