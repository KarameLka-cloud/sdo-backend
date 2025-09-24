<?php

namespace App\Http\Controllers\Edo;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Edo\EdoTest;
use Illuminate\Http\Request;

class EdoTestController extends Controller
{
    public function index(): JsonResponse
    {
        $tests = EdoTest::orderBy('date_end', 'desc')->get();
        return response()->json($tests);
    }

    public function store(Request $request): JsonResponse
    {
        $test = EdoTest::create($request->all());
        return response()->json($test);
    }

    public function show($id): JsonResponse
    {
        $test = EdoTest::findOrFail($id);
        return response()->json($test);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $test = EdoTest::findOrFail($id);
        $test->update($request->all());
        return response()->json($test);
    }

    public function destroy($id): JsonResponse
    {
        $test = EdoTest::findOrFail($id);
        $test->delete();
        return response()->json(['message' => 'Test deleted']);
    }
}
