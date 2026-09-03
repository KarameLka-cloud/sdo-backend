<?php

namespace App\Http\Controllers\EmployeeDirectory;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeSearchRequest;
use App\Services\Ldap\LDAPNavigator;
use Illuminate\Http\JsonResponse;
use Throwable;

class EmployeeDirectoryController extends Controller
{
    public function __construct(
        private readonly LDAPNavigator $ldapNavigator,
    ) {}

    public function search(EmployeeSearchRequest $request): JsonResponse
    {
        $searchList = $this->ldapNavigator->parseSearchGroups($request->validated('q'));

        if ($searchList === []) {
            return $this->respond([]);
        }

        try {
            return $this->respond(
                $this->ldapNavigator->search($searchList, $request->boolean('with_photo'))
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Не удалось выполнить поиск в справочнике сотрудников.',
            ], 502);
        }
    }

    private function respond(array $entries): JsonResponse
    {
        return response()->json([
            'data' => $entries,
            'attributes' => $this->ldapNavigator->getAttributeList(),
        ]);
    }
}
