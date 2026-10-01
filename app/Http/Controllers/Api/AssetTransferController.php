<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetTransferStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssetTransferResource;
use App\Models\AssetTransfer;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssetTransferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = Validator::make($request->all(), [
            'assetId' => 'nullable|integer|exists:assets,id',
            'status'  => 'nullable|string|Rule::enum(' . AssetTransferStatus::class . ')',
        ]);

        if ($filters->fails()) {
            return $this->sendError('Validation errors.', $filters->errors(), 422);
        }

        try {
            $query = AssetTransfer::with(
                'asset',
                'fromLocation',
                'toLocation',
                'fromEmployee',
                'toEmployee',
                'createdBy'
            );

            if ($request->filled('assetId')) {
                $query->where('assetId', $request->input('assetId'));
            }
            if ($request->filled('status')) {
                $query->where('Status', $request->input('status'));
            }

            $transfers = $query->orderByDesc('id')->paginate(15);

            $result = [
                'items' => AssetTransferResource::collection($transfers->items()),
                'meta'  => [
                    'current_page' => $transfers->currentPage(),
                    'per_page'     => $transfers->perPage(),
                    'total'        => $transfers->total(),
                    'last_page'    => $transfers->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $transfers->url(1),
                    'last_page_url'  => $transfers->url($transfers->lastPage()),
                    'next_page_url'  => $transfers->nextPageUrl(),
                    'prev_page_url'  => $transfers->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Asset transfers retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve asset transfers.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'assetId'        => 'required|integer|exists:assets,id',
            'fromLocationId' => 'nullable|integer|exists:locations,id',
            'toLocationId'   => 'nullable|required_without_all:toEmployeeId|integer|exists:locations,id',
            'fromEmployeeId' => 'nullable|integer|exists:employees,id',
            'toEmployeeId'   => 'nullable|required_without_all:toLocationId|integer|exists:employees,id',
            'TransferDate'   => 'required|date',
            'Reason'         => 'nullable|string|max:255',
            'Status'         => ['nullable', 'string', Rule::enum(AssetTransferStatus::class)],
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $data = $validate->validated();

            $transfer = AssetTransfer::create([
                ...$data,
                'Status'         => $data['Status'] ?? AssetTransferStatus::PENDING->value,
                'CreatedByUserID' => $request->user()->id,
            ]);

            return $this->sendResponse([
                'item'  => new AssetTransferResource($transfer->load('asset', 'toLocation', 'toEmployee')),
                'meta'  => [],
                'links' => [],
            ], 'Asset transfer created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save asset transfer.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show(AssetTransfer $assetTransfer): JsonResponse
    {
        $transfer = $assetTransfer->load(
            'asset',
            'fromLocation',
            'toLocation',
            'fromEmployee',
            'toEmployee',
            'createdBy'
        );

        return $this->sendResponse([
            'item'  => new AssetTransferResource($transfer),
            'meta'  => [],
            'links' => [],
        ], 'Asset transfer retrieved successfully.');
    }

    public function update(Request $request, AssetTransfer $assetTransfer): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'fromLocationId' => 'nullable|integer|exists:locations,id',
            'toLocationId'   => 'nullable|integer|exists:locations,id',
            'fromEmployeeId' => 'nullable|integer|exists:employees,id',
            'toEmployeeId'   => 'nullable|integer|exists:employees,id',
            'TransferDate'   => 'nullable|date',
            'Reason'         => 'nullable|string|max:255',
            'Status'         => 'nullable|string|Rule::enum(' . AssetTransferStatus::class . ')',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $assetTransfer->update($validate->validated());

            return $this->sendResponse([
                'item' => new AssetTransferResource($assetTransfer->load('asset', 'toLocation', 'toEmployee')),
            ], 'Asset transfer updated successfully.');
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not update asset transfer.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function destroy(AssetTransfer $assetTransfer): JsonResponse
    {
        try {
            $assetTransfer->delete();

            return $this->sendResponse([], 'Asset transfer deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}