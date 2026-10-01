<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        try {
            $locations = Location::withCount('assets')
                ->with('organization')
                ->paginate(15);

            $result = [
                'items' => LocationResource::collection($locations->items()),
                'meta'  => [
                    'current_page' => $locations->currentPage(),
                    'per_page'     => $locations->perPage(),
                    'total'        => $locations->total(),
                    'last_page'    => $locations->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $locations->url(1),
                    'last_page_url'  => $locations->url($locations->lastPage()),
                    'next_page_url'  => $locations->nextPageUrl(),
                    'prev_page_url'  => $locations->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Locations retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve locations.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name'            => 'required|string|max:255|unique:locations,name',
            'code'            => 'nullable|string|max:50|unique:locations,code',
            'description'     => 'nullable|string',
            'organization_id' => 'nullable|integer|exists:organizations,id',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $location = Location::create([
                'name'            => $request->name,
                'code'            => $request->code ?: generateUniqueCode(5, 'LOC-'),
                'description'     => $request->description,
                'organization_id' => $request->organization_id,
            ]);

            $result = [
                'item'  => new LocationResource($location->load('organization')),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Location created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save location.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $location = Location::withCount('assets')->with('organization')->find($id);
        if (!$location) {
            return $this->sendError('Location not found.', [], 404);
        }

        $result = [
            'item'  => new LocationResource($location),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Location retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $location = Location::find($id);
        if (!$location) {
            return $this->sendError('Location not found.', [], 404);
        }

        $validate = Validator::make($request->all(), [
            'name'            => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location->id)],
            'code'            => ['nullable', 'string', 'max:50', Rule::unique('locations', 'code')->ignore($location->id)],
            'description'     => 'nullable|string',
            'organization_id' => 'nullable|integer|exists:organizations,id',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $location->update([
                'name'            => $request->name,
                'code'            => $request->filled('code') ? $request->code : $location->getOriginal('code'),
                'description'     => $request->description,
                'organization_id' => $request->organization_id,
            ]);

            $result = [
                'item'  => new LocationResource($location->loadCount('assets')->load('organization')),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Location updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Update failed.', [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $location = Location::find($id);
        if (!$location) {
            return $this->sendError('Location not found.', [], 404);
        }

        try {
            $location->delete();

            return $this->sendResponse([], 'Location deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}