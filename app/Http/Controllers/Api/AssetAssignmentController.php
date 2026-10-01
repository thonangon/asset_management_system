<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssetAssignmentResource;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssetAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AssetAssignment::with('asset', 'assignedTo', 'fromLocation', 'toLocation', 'createdBy');

            if ($request->filled('assetId')) {
                $query->where('assetId', $request->input('assetId'));
            }
            if ($request->filled('assignedToEmployeeId')) {
                $query->where('assignedToEmployeeId', $request->input('assignedToEmployeeId'));
            }

            $assignments = $query->paginate(15);

            $result = [
                'items' => AssetAssignmentResource::collection($assignments->items()),
                'meta'  => [
                    'current_page' => $assignments->currentPage(),
                    'per_page'     => $assignments->perPage(),
                    'total'        => $assignments->total(),
                    'last_page'    => $assignments->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $assignments->url(1),
                    'last_page_url'  => $assignments->url($assignments->lastPage()),
                    'next_page_url'  => $assignments->nextPageUrl(),
                    'prev_page_url'  => $assignments->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Asset assignments retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve asset assignments.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'assetId'              => 'required|integer|exists:assets,id',
            'assignedToEmployeeId' => 'required|integer|exists:employees,id',
            'fromLocationId'       => 'nullable|integer|exists:locations,id',
            'toLocationId'         => 'nullable|integer|exists:locations,id',
            'assignmentDate'       => 'required|date',
            'expectedReturnDate'   => 'nullable|date',
            'returnDate'           => 'nullable|date',
            'status'               => 'nullable|string|max:50',
            'notes'                => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $assignment = AssetAssignment::create([
                ...$validate->validated(),
                'createdByUserId' => $request->user()->id,
            ]);

            $asset = Asset::findOrFail($assignment->assetId);
            $asset->update(array_filter([
                'current_location_id' => $assignment->toLocationId,
                'status'              => AssetStatus::ASSIGNED->value,
            ], fn ($value) => $value !== null));

            return $this->sendResponse([
                'item'  => new AssetAssignmentResource($assignment->load('asset', 'assignedTo')),
                'meta'  => [],
                'links' => [],
            ], 'Asset assignment created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save asset assignment.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show(AssetAssignment $assetAssignment): JsonResponse
    {
        $assignment = $assetAssignment->load('asset', 'assignedTo', 'fromLocation', 'toLocation', 'createdBy');

        return $this->sendResponse([
            'item'  => new AssetAssignmentResource($assignment),
            'meta'  => [],
            'links' => [],
        ], 'Asset assignment retrieved successfully.');
    }

    public function update(Request $request, AssetAssignment $assetAssignment): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'fromLocationId'     => 'nullable|integer|exists:locations,id',
            'toLocationId'       => 'nullable|integer|exists:locations,id',
            'expectedReturnDate' => 'nullable|date',
            'returnDate'         => 'nullable|date',
            'status'             => 'nullable|string|max:50',
            'notes'              => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $assetAssignment->update($validate->validated());

            return $this->sendResponse([
                'item' => new AssetAssignmentResource($assetAssignment->load('asset', 'assignedTo')),
            ], 'Asset assignment updated successfully.');
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not update asset assignment.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function destroy(AssetAssignment $assetAssignment): JsonResponse
    {
        try {
            $assetAssignment->delete();

            return $this->sendResponse([], 'Asset assignment deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}