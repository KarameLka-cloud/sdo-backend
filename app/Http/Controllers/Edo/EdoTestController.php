<?php

namespace App\Http\Controllers\Edo;

use App\Http\Requests\TestRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Edo\EdoTest;

class EdoTestController extends Controller
{
    public function index(): JsonResponse
    {
        $tests = EdoTest::orderBy('date', 'desc')->get();
        return response()->json($tests);
    }

    public function store(TestRequest $request): JsonResponse
    {
        $test = EdoTest::create($request->validated());
        return response()->json($test);
    }

    public function show($id): JsonResponse
    {
        $test = EdoTest::findOrFail($id);
        return response()->json($test);
    }

    public function update(TestRequest $request, $id): JsonResponse
    {
        $test = EdoTest::findOrFail($id);
        $test->update($request->validated());
        return response()->json($test);
    }

    public function destroy($id): JsonResponse
    {
        $test = EdoTest::findOrFail($id);
        $test->delete();
        return response()->json(['message' => 'Test deleted']);
    }
}
