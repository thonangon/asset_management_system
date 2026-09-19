<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Warranty;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Asset::with([
                'assetCategory',
                'location',
                'warranty',
                'depreciations' => fn ($q) => $q->orderBy('period_start'),
            ]);

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            if ($status = $request->query('status')) {
                $validStatuses = array_column(AssetStatus::cases(), 'value');
                if (!in_array($status, $validStatuses)) {
                    return $this->sendError('Invalid asset status filter.', [], 422);
                }
                $query->where('status', $status);
            }

            if ($categoryId = $request->query('asset_category_id')) {
                $query->where('asset_category_id', $categoryId);
            }

            if ($locationId = $request->query('current_location_id')) {
                $query->where('current_location_id', $locationId);
            }

            if ($organizationId = $request->query('organization_id')) {
                $query->whereHas('location', fn ($q) => $q->where('organization_id', $organizationId));
            }

            $assets = $query->paginate(15);

            $result = [
                'items' => AssetResource::collection($assets->items()),
                'meta'  => [
                    'current_page' => $assets->currentPage(),
                    'per_page'     => $assets->perPage(),
                    'total'        => $assets->total(),
                    'last_page'    => $assets->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $assets->url(1),
                    'last_page_url'  => $assets->url($assets->lastPage()),
                    'next_page_url'  => $assets->nextPageUrl(),
                    'prev_page_url'  => $assets->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Assets retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve assets.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'asset_tag'              => 'nullable|string|max:100|unique:assets,asset_tag',
            'serial_number'          => 'nullable|string|max:100',
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'asset_category_id'      => 'required|integer|exists:asset_categories,id',
            'current_location_id'    => 'required|integer|exists:locations,id',
            'purchase_cost'          => 'required|numeric|min:0',
            'purchase_date'          => 'required|date',
            'useful_life_years'      => 'required|integer|min:1|max:100',
            'status'                 => 'nullable|in:' . implode(',', array_column(AssetStatus::cases(), 'value')),
            'warranty'               => 'nullable|array',
            'warranty.provider'      => 'nullable|string|max:255',
            'warranty.start_date'    => 'nullable|date',
            'warranty.end_date'      => 'nullable|date|after_or_equal:warranty.start_date',
            'warranty.coverage_details' => 'nullable|string|max:1000',
            'warranty.policy_number' => 'nullable|string|max:100',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $category = AssetCategory::find($request->asset_category_id);
            if (!$category) throw_msg('Asset category not found', 404);

            $location = Location::find($request->current_location_id);
            if (!$location) throw_msg('Location not found', 404);

            $userId = $request->user()?->id;

            DB::beginTransaction();

            try {
                $asset = Asset::create([
                    'asset_tag'          => $request->asset_tag ?: generateUniqueCode(8, 'AST-'),
                    'serial_number'      => $request->serial_number,
                    'name'               => $request->name,
                    'description'        => $request->description,
                    'asset_category_id'  => $request->asset_category_id,
                    'current_location_id' => $request->current_location_id,
                    'purchase_cost'      => $request->purchase_cost,
                    'purchase_date'      => $request->purchase_date,
                    'useful_life_years'  => $request->useful_life_years,
                    'status'             => $request->status ?? AssetStatus::AVAILABLE->value,
                ]);

                Warranty::create([
                    'asset_id'           => $asset->id,
                    'provider'           => $request->input('warranty.provider'),
                    'start_date'         => $request->input('warranty.start_date'),
                    'end_date'           => $request->input('warranty.end_date'),
                    'coverage_details'   => $request->input('warranty.coverage_details'),
                    'policy_number'      => $request->input('warranty.policy_number'),
                    'status'             => 'active',
                    'created_by_user_id' => $userId,
                ]);

                foreach ($this->buildDepreciationRecords($asset, $userId) as $record) {
                    $asset->depreciations()->create($record);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $asset->load(['assetCategory', 'location', 'warranty', 'depreciations']);

            $responseData = [
                'success' => true,
                'data'    => new AssetResource($asset),
            ];

            return $this->sendResponse($responseData, 'Asset created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save asset.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $asset = Asset::with(['assetCategory', 'location', 'warranty', 'depreciations' => fn ($q) => $q->orderBy('period_start')])->find($id);
        if (!$asset) {
            return $this->sendError('Asset not found.', [], 404);
        }

        $result = [
            'item'  => new AssetResource($asset),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Asset retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return $this->sendError('Asset not found.', [], 404);
        }

        $validate = Validator::make($request->all(), [
            'asset_tag'              => ['nullable', 'string', 'max:100', Rule::unique('assets', 'asset_tag')->ignore($asset->id)],
            'serial_number'          => 'nullable|string|max:100',
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'asset_category_id'      => 'required|integer|exists:asset_categories,id',
            'current_location_id'    => 'required|integer|exists:locations,id',
            'purchase_cost'          => 'required|numeric|min:0',
            'purchase_date'          => 'required|date',
            'useful_life_years'      => 'required|integer|min:1|max:100',
            'status'                 => 'nullable|in:' . implode(',', array_column(AssetStatus::cases(), 'value')),
            'warranty'               => 'nullable|array',
            'warranty.provider'      => 'nullable|string|max:255',
            'warranty.start_date'    => 'nullable|date',
            'warranty.end_date'      => 'nullable|date|after_or_equal:warranty.start_date',
            'warranty.coverage_details' => 'nullable|string|max:1000',
            'warranty.policy_number' => 'nullable|string|max:100',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $userId = $request->user()?->id;

            DB::beginTransaction();

            try {
                $asset->update([
                    'asset_tag'          => $request->asset_tag ?: $asset->getOriginal('asset_tag'),
                    'serial_number'      => $request->serial_number,
                    'name'               => $request->name,
                    'description'        => $request->description,
                    'asset_category_id'  => $request->asset_category_id,
                    'current_location_id' => $request->current_location_id,
                    'purchase_cost'      => $request->purchase_cost,
                    'purchase_date'      => $request->purchase_date,
                    'useful_life_years'  => $request->useful_life_years,
                    'status'             => $request->status ?? $asset->getOriginal('status'),
                ]);

                $warrantyData = [
                    'provider'         => $request->input('warranty.provider'),
                    'start_date'       => $request->input('warranty.start_date'),
                    'end_date'         => $request->input('warranty.end_date'),
                    'coverage_details' => $request->input('warranty.coverage_details'),
                    'policy_number'    => $request->input('warranty.policy_number'),
                ];

                if ($warranty = $asset->warranty) {
                    $warranty->update($warrantyData);
                } else {
                    Warranty::create(array_merge($warrantyData, [
                        'asset_id'           => $asset->id,
                        'status'             => 'active',
                        'created_by_user_id' => $userId,
                    ]));
                }

                $asset->depreciations()->delete();

                foreach ($this->buildDepreciationRecords($asset, $userId) as $record) {
                    $asset->depreciations()->create($record);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $asset->load(['assetCategory', 'location', 'warranty', 'depreciations']);

            $result = [
                'item'  => new AssetResource($asset),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Asset updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Update failed.', [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $asset = Asset::find($id);
        if (!$asset) {
            return $this->sendError('Asset not found.', [], 404);
        }

        try {
            $asset->delete();

            return $this->sendResponse([], 'Asset deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }

    private function buildDepreciationRecords(Asset $asset, ?int $userId): array
    {
        $records = [];
        $purchaseDate = $asset->purchase_date;
        $years = (int) $asset->useful_life_years;
        $cost = (float) $asset->purchase_cost;

        if (!$purchaseDate || $years <= 0 || $cost <= 0) {
            return $records;
        }

        $annual = round($cost / $years, 2);

        for ($i = 0; $i < $years; $i++) {
            $opening = round($cost - ($i * $annual), 2);
            $amount = ($i === $years - 1) ? $opening : $annual;

            $records[] = [
                'period_start'        => $purchaseDate->copy()->addYears($i)->toDateString(),
                'period_end'          => $purchaseDate->copy()->addYears($i + 1)->subDay()->toDateString(),
                'opening_book_value'  => $opening,
                'depreciation_amount' => $amount,
                'closing_book_value'  => round($opening - $amount, 2),
                'method'              => 'straight-line',
                'user_id'             => $userId,
            ];
        }

        return $records;
    }
}