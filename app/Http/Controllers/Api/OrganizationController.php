<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index()
    {
        try {
            $organizations = Organization::withCount('departments')->paginate(15);

            $result = [
                'items' => OrganizationResource::collection($organizations->items()),
                'meta'  => [
                    'current_page' => $organizations->currentPage(),
                    'per_page'     => $organizations->perPage(),
                    'total'        => $organizations->total(),
                    'last_page'    => $organizations->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $organizations->url(1),
                    'last_page_url'  => $organizations->url($organizations->lastPage()),
                    'next_page_url'  => $organizations->nextPageUrl(),
                    'prev_page_url'  => $organizations->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Organizations retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve organizations.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'indentifier' => 'required|string|max:255|unique:organizations,indentifier',
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|string|max:255',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $organization = Organization::create([
                'indentifier' => $request->indentifier,
                'name'        => $request->name,
                'slug'        => Str::slug($request->name),
                'address'     => $request->address,
                'description' => $request->description,
                'logo'        => $request->logo,
            ]);

            $result = [
                'item'  => new OrganizationResource($organization),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Organization created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save organization.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $organization = Organization::withCount('departments')->find($id);
        if (!$organization) {
            return $this->sendError('Organization not found.', [], 404);
        }

        $result = [
            'item'  => new OrganizationResource($organization),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Organization retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $organization = Organization::find($id);
        if (!$organization) {
            return $this->sendError('Organization not found.', [], 404);
        }

        $validate = Validator::make($request->all(), [
            'indentifier' => ['required', 'string', 'max:255', Rule::unique('organizations', 'indentifier')->ignore($organization->id)],
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|string|max:255',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $organization->update([
                'indentifier' => $request->indentifier,
                'name'        => $request->name,
                'slug'        => Str::slug($request->name),
                'address'     => $request->address,
                'description' => $request->description,
                'logo'        => $request->logo,
            ]);

            $result = [
                'item'  => new OrganizationResource($organization->loadCount('departments')),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Organization updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Update failed.', [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $organization = Organization::find($id);
        if (!$organization) {
            return $this->sendError('Organization not found.', [], 404);
        }

        try {
            $organization->delete();

            return $this->sendResponse([], 'Organization deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}
