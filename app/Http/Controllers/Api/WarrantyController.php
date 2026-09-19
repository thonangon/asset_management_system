<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WarrantyResource;
use App\Models\Warranty;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Warranty::with('asset');

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('provider', 'like', "%{$search}%")
                        ->orWhere('policy_number', 'like', "%{$search}%")
                        ->orWhere('coverage_details', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($assetQuery) use ($search) {
                            $assetQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('asset_tag', 'like', "%{$search}%");
                        });
                });
            }

            if ($assetId = $request->query('asset_id')) {
                $query->where('asset_id', $assetId);
            }

            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }

            $warranties = $query->orderByDesc('id')->paginate(15);

            $result = [
                'items' => WarrantyResource::collection($warranties->items()),
                'meta'  => [
                    'current_page' => $warranties->currentPage(),
                    'per_page'     => $warranties->perPage(),
                    'total'        => $warranties->total(),
                    'last_page'    => $warranties->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $warranties->url(1),
                    'last_page_url'  => $warranties->url($warranties->lastPage()),
                    'next_page_url'  => $warranties->nextPageUrl(),
                    'prev_page_url'  => $warranties->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Warranties retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve warranties.', [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $warranty = Warranty::with('asset')->find($id);
        if (!$warranty) {
            return $this->sendError('Warranty not found.', [], 404);
        }

        $result = [
            'item'  => new WarrantyResource($warranty),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Warranty retrieved successfully.');
    }
}