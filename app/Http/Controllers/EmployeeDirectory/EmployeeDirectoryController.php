<?php

namespace App\Http\Controllers\EmployeeDirectory;

use App\Http\Controllers\Controller;
use App\Services\Ldap\LDAPNavigator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EmployeeDirectoryController extends Controller
{
    public function __construct(
        private readonly LDAPNavigator $ldapNavigator,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
            'with_photo' => ['sometimes', 'boolean'],
        ]);

        $withPhoto = $request->boolean('with_photo', false);
        $searchList = $this->ldapNavigator->parseSearchGroups($validated['q']);

        if ($searchList === []) {
            return response()->json([
                'data' => [],
                'attributes' => $this->ldapNavigator->getAttributeList(),
            ]);
        }

        try {
            $entries = $this->ldapNavigator->search($searchList, $withPhoto);

            return response()->json([
                'data' => $entries,
                'attributes' => $this->ldapNavigator->getAttributeList(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Не удалось выполнить поиск в справочнике сотрудников.',
            ], 502);
        }
    }
}
