<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            $departments = Department::withCount('employees')->paginate(15);

            $result = [
                'items' => DepartmentResource::collection($departments->items()),
                'meta'  => [
                    'current_page' => $departments->currentPage(),
                    'per_page'     => $departments->perPage(),
                    'total'        => $departments->total(),
                    'last_page'    => $departments->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $departments->url(1),
                    'last_page_url'  => $departments->url($departments->lastPage()),
                    'next_page_url'  => $departments->nextPageUrl(),
                    'prev_page_url'  => $departments->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Departments retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve departments.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'Name'        => 'required|string|max:100|unique:departments,Name',
            'Code'        => 'nullable|string|max:20|unique:departments,Code',
            'description' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $department = Department::create([
                'Name'        => $request->Name,
                'Code'        => $request->Code ?: generateUniqueCode(5, 'DEPT-'),
                'description' => $request->description,
            ]);

            $responseData = [
                'success' => true,
                'data'    => new DepartmentResource($department),
            ];

            return $this->sendResponse($responseData, 'Department created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save department.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $department = Department::withCount('employees')->find($id);
        if (!$department) {
            return $this->sendError('Department not found.', [], 404);
        }

        $result = [
            'item'  => new DepartmentResource($department),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Department retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $department = Department::find($id);
        if (!$department) {
            return $this->sendError('Department not found.', [], 404);
        }

        $validate = Validator::make($request->all(), [
            'Name'        => ['required', 'string', 'max:100', Rule::unique('departments', 'Name')->ignore($department->id)],
            'Code'        => ['nullable', 'string', 'max:20', Rule::unique('departments', 'Code')->ignore($department->id)],
            'description' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $department->update([
                'Name'        => $request->Name,
                'Code'        => $request->filled('Code') ? $request->Code : $department->getOriginal('Code'),
                'description' => $request->description,
            ]);

            $result = [
                'item'  => new DepartmentResource($department->loadCount('employees')),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Department updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Update failed.', [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $department = Department::find($id);
        if (!$department) {
            return $this->sendError('Department not found.', [], 404);
        }

        try {
            $department->delete();

            return $this->sendResponse([], 'Department deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}
