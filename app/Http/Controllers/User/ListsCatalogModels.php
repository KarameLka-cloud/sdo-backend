<?php

namespace App\Http\Controllers\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

trait ListsCatalogModels
{
    /** @param class-string<Model> $modelClass */
    protected function listCatalog(string $modelClass): JsonResponse
    {
        return response()->json($modelClass::query()->orderBy('name')->get());
    }
}
