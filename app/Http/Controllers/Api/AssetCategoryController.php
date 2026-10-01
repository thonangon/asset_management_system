<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetCategoryResource;
use App\Models\AssetCategory;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssetCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = AssetCategory::withCount('assets')->paginate(15);

            $result = [
                'items' => AssetCategoryResource::collection($categories->items()),
                'meta'  => [
                    'current_page' => $categories->currentPage(),
                    'per_page'     => $categories->perPage(),
                    'total'        => $categories->total(),
                    'last_page'    => $categories->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $categories->url(1),
                    'last_page_url'  => $categories->url($categories->lastPage()),
                    'next_page_url'  => $categories->nextPageUrl(),
                    'prev_page_url'  => $categories->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Asset categories retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve asset categories.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:asset_categories,name',
            'code'        => 'nullable|string|max:50|unique:asset_categories,code',
            'description' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $category = AssetCategory::create([
                'name'        => $request->name,
                'code'        => $request->code ?: generateUniqueCode(5, $request->name),
                'description' => $request->description,
            ]);

            $result = [
                'item'  => new AssetCategoryResource($category),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Asset category created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save asset category.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $category = AssetCategory::withCount('assets')->find($id);
        if (!$category) {
            return $this->sendError('Asset category not found.', [], 404);
        }

        $result = [
            'item'  => new AssetCategoryResource($category),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Asset category retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $category = AssetCategory::find($id);
        if (!$category) {
            return $this->sendError('Asset category not found.', [], 404);
        }

        $validate = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255', Rule::unique('asset_categories', 'name')->ignore($category->id)],
            'code'        => ['nullable', 'string', 'max:50', Rule::unique('asset_categories', 'code')->ignore($category->id)],
            'description' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $category->update([
                'name'        => $request->name,
                'code'        => $request->filled('code') ? $request->code : $category->getOriginal('code'),
                'description' => $request->description,
            ]);

            $result = [
                'item'  => new AssetCategoryResource($category->loadCount('assets')),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Asset category updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Update failed.', [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $category = AssetCategory::find($id);
        if (!$category) {
            return $this->sendError('Asset category not found.', [], 404);
        }

        try {
            $category->delete();

            return $this->sendResponse([], 'Asset category deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}