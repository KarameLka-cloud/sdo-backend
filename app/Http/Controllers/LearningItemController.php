<?php

namespace App\Http\Controllers;

use App\Http\Requests\LearningItemIndexRequest;
use App\Http\Requests\LearningItemRequest;
use App\Models\LearningItem;
use Illuminate\Http\JsonResponse;

class LearningItemController extends Controller
{
    private const RELATIONS = ['departmentRelation', 'positionRelation'];

    /** Types where the start time is meaningful and worth ordering by. */
    private const TIMED_TYPES = [LearningItem::TYPE_EVENT, LearningItem::TYPE_WEBINAR];

    public function index(LearningItemIndexRequest $request): JsonResponse
    {
        ['category' => $category, 'type' => $type] = $request->validated();

        $items = LearningItem::query()
            ->with(self::RELATIONS)
            ->where('category', $category)
            ->where('type', $type)
            ->orderByDesc('date')
            ->when(
                in_array($type, self::TIMED_TYPES, true),
                fn ($query) => $query->orderBy('time')
            )
            ->get();

        return response()->json($items);
    }

    public function store(LearningItemRequest $request): JsonResponse
    {
        $item = LearningItem::create($request->validated());

        return response()->json($item->load(self::RELATIONS), 201);
    }

    public function update(LearningItemRequest $request, LearningItem $learningItem): JsonResponse
    {
        $learningItem->update($request->validated());

        return response()->json($learningItem->load(self::RELATIONS));
    }

    public function destroy(LearningItem $learningItem): JsonResponse
    {
        $learningItem->delete();

        return response()->json(['message' => 'Материал удалён']);
    }
}
