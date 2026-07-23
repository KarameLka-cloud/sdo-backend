<?php

namespace App\Http\Controllers;

use App\Http\Requests\LearningItemRequest;
use App\Models\LearningItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LearningItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(LearningItem::CATEGORIES)],
            'type' => ['required', Rule::in(LearningItem::TYPES)],
        ]);

        $items = LearningItem::query()
            ->where('category', $validated['category'])
            ->where('type', $validated['type'])
            ->orderBy('date', 'desc')
            ->when(
                in_array($validated['type'], [
                    LearningItem::TYPE_EVENT,
                    LearningItem::TYPE_WEBINAR,
                ], true),
                fn ($query) => $query->orderBy('time')
            )
            ->get();

        return response()->json($items);
    }

    public function store(LearningItemRequest $request): JsonResponse
    {
        $item = LearningItem::create($request->validated());

        return response()->json($item);
    }

    public function show($id): JsonResponse
    {
        $item = LearningItem::findOrFail($id);

        return response()->json($item);
    }

    public function update(LearningItemRequest $request, $id): JsonResponse
    {
        $item = LearningItem::findOrFail($id);
        $item->update($request->validated());

        return response()->json($item);
    }

    public function destroy($id): JsonResponse
    {
        $item = LearningItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Learning item deleted']);
    }
}
