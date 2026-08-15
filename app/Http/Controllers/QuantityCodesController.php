<?php

namespace App\Http\Controllers;

use App\Services\TaxlyResourceOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuantityCodesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = max((int) $request->integer('page', 1), 1);
        $search = $request->string('q')->trim()->value() ?: null;

        $result = TaxlyResourceOptions::quantityCodesSearch($search, $page);

        if ($result === null) {
            return response()->json(['message' => 'Failed to fetch quantity codes.'], 500);
        }

        return response()->json($result);
    }
}
