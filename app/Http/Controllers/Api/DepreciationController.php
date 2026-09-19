<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepreciationResource;
use App\Models\Depreciation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepreciationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Depreciation::with('asset');

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('method', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($assetQuery) use ($search) {
                            $assetQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('asset_tag', 'like', "%{$search}%");
                        });
                });
            }

            if ($assetId = $request->query('asset_id')) {
                $query->where('asset_id', $assetId);
            }

            if ($method = $request->query('method')) {
                $query->where('method', $method);
            }

            $depreciations = $query->orderByDesc('period_start')->paginate(15);

            $result = [
                'items' => DepreciationResource::collection($depreciations->items()),
                'meta'  => [
                    'current_page' => $depreciations->currentPage(),
                    'per_page'     => $depreciations->perPage(),
                    'total'        => $depreciations->total(),
                    'last_page'    => $depreciations->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $depreciations->url(1),
                    'last_page_url'  => $depreciations->url($depreciations->lastPage()),
                    'next_page_url'  => $depreciations->nextPageUrl(),
                    'prev_page_url'  => $depreciations->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Depreciations retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve depreciations.', [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $depreciation = Depreciation::with('asset')->find($id);
        if (!$depreciation) {
            return $this->sendError('Depreciation not found.', [], 404);
        }

        $result = [
            'item'  => new DepreciationResource($depreciation),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Depreciation retrieved successfully.');
    }
}