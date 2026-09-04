<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Position;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    use ListsCatalogModels;

    public function index(): JsonResponse
    {
        return $this->listCatalog(Position::class);
    }

    public function show(Position $position): JsonResponse
    {
        return response()->json($position);
    }
}
