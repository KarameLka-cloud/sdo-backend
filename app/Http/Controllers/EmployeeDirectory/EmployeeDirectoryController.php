<?php

namespace App\Http\Controllers\EmployeeDirectory;

use App\Http\Controllers\Controller;
use App\Services\Ldap\LDAPNavigator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EmployeeDirectoryController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
            'with_photo' => ['sometimes', 'boolean'],
        ]);

        $withPhoto = $request->boolean('with_photo', true);
        $searchList = $this->parseSearchGroups($validated['q']);

        if ($searchList === []) {
            return response()->json([
                'data' => [],
                'attributes' => (new LDAPNavigator())->getAttributeList(),
            ]);
        }

        try {
            $navigator = new LDAPNavigator();
            $entries = $navigator->search($searchList, $withPhoto);

            return response()->json([
                'data' => $entries,
                'attributes' => $navigator->getAttributeList(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Не удалось выполнить поиск в справочнике сотрудников.',
            ], 502);
        }
    }

    /**
     * "Менеджер + Иркутск 1" → [["Менеджер"], ["Иркутск", "1"]]
     * "Менеджер Иркутск Гоголя" → [["Менеджер"], ["Иркутск"], ["Гоголя"]]
     *
     * Сегменты через + — AND. Слова внутри сегмента должны быть в одном атрибуте.
     *
     * @return list<list<string>>
     */
    private function parseSearchGroups(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $hasSeparators = (bool) preg_match('/[+;|]/u', $query);

        if ($hasSeparators) {
            $segments = preg_split('/\s*[+;|]+\s*/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } else {
            $segments = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $groups = [];
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            if ($hasSeparators) {
                $words = preg_split('/\s+/u', $segment, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $words = array_values(array_filter($words, static fn (string $word): bool => $word !== ''));
                if ($words !== []) {
                    $groups[] = $words;
                }
            } else {
                $groups[] = [$segment];
            }
        }

        return $groups;
    }
}
