<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeesResource;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Occupation;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $employees = Employee::with('department')->paginate(15);

            $result = [
                'items' => EmployeesResource::collection($employees->items()),
                'meta'  => [
                    'current_page' => $employees->currentPage(),
                    'per_page'     => $employees->perPage(),
                    'total'        => $employees->total(),
                    'last_page'    => $employees->lastPage(),
                ],
                'links' => [
                    'first_page_url' => $employees->url(1),
                    'last_page_url'  => $employees->url($employees->lastPage()),
                    'next_page_url'  => $employees->nextPageUrl(),
                    'prev_page_url'  => $employees->previousPageUrl(),
                ],
            ];

            return $this->sendResponse($result, 'Employees retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Could not retrieve employees.', [], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'firstname'    => 'required|string|max:100',
            'lastname'     => 'required|string|max:100',
            'email'        => 'required|email|max:255|unique:employees,email',
            'phone'        => 'nullable|string|max:20|unique:employees,phone',
            'departmentId' => 'required|integer|exists:departments,id',
            'occupation_id' => 'nullable|integer|exists:occupations,id',
            'gender'       => 'required|in:male,female',
            'birthdate'    => 'nullable|date',
            'photo_path'   => 'nullable|string|max:255',
            'username'     => 'nullable|string|max:255|unique:users,username',
            'password'     => 'required|string|min:8',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $department = Department::find($request->departmentId);
            if (!$department) throw_msg('Department not found', 404);

            $occupation = null;
            if ($request->occupation_id) {
                $occupation = Occupation::find($request->occupation_id);
                if (!$occupation) throw_msg('Occupation not found', 404);
            }
            $employee = null;
            $user = null;

            DB::beginTransaction();

            try {
                $employee = Employee::create([
                    'EmployeeCode' => generateUniqueCode(6, 'EMP-'),
                    'DepartmentID' => $request->departmentId,
                    'FirstName'    => $request->firstname,
                    'LastName'     => $request->lastname,
                    'Email'        => $request->email,
                    'Phone'        => $request->phone,
                    'Status'       => 'active',
                    'birthdate'    => $request->birthdate,
                    'gender'       => $request->gender,
                    'photo_path'   => $request->photo_path,
                    'occupation_id' => $request->occupation_id,
                ]);

                $role = Role::findByName(Roles::EMPLOYEE->value, 'sanctum');

                $user = User::create([
                    'name'           => $request->firstname . ' ' . $request->lastname,
                    'email'          => $request->email,
                    'username'       => $request->username,
                    'password'       => Hash::make($request->password),
                    'EmployeeID'     => $employee->id,
                    'must_reset_password' => true,
                    'status'         => 'active',
                    'email_verified_at' => now(),
                    'role_id'        => $role?->id,
                ]);

                if ($role) {
                    $user->assignRole($role);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $responseData = [
                'success' => true,
                'data'    => new EmployeesResource($employee->load(['department', 'occupation'])),
            ];

            return $this->sendResponse($responseData, 'Employee created successfully.', 201);
        } catch (QueryException $e) {
            return $this->sendError('Database error: Could not save employee.', [], 422);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($id): JsonResponse
    {
        $employee = Employee::with(['department', 'occupation'])->find($id);
        if (!$employee) {
            return $this->sendError('Employee not found.', [], 404);
        }

        $result = [
            'item'  => new EmployeesResource($employee),
            'meta'  => [],
            'links' => [],
        ];

        return $this->sendResponse($result, 'Employee retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return $this->sendError('Employee not found.', [], 404);
        }

        $validate = Validator::make($request->all(), [
            'FirstName'    => 'required|string|max:100',
            'LastName'     => 'required|string|max:100',
            'Email'        => ['required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id)],
            'Phone'        => ['nullable', 'string', 'max:20', Rule::unique('employees', 'phone')->ignore($employee->id)],
            'DepartmentID' => 'required|integer|exists:departments,id',
            'occupation_id' => 'nullable|integer|exists:occupations,id',
            'Status'       => 'required|in:active,inactive,terminated',
            'gender'       => 'required|in:male,female',
            'birthdate'    => 'nullable|date',
            'photo_path'   => 'nullable|string|max:255',
        ]);

        if ($validate->fails()) {
            return $this->sendError('Validation errors.', $validate->errors(), 422);
        }

        try {
            $department = Department::find($request->DepartmentID);
            if (!$department) throw_msg('Department not found', 404);

            $employee->update([
                'DepartmentID' => $request->DepartmentID,
                'FirstName'    => $request->FirstName,
                'LastName'     => $request->LastName,
                'Email'        => $request->Email,
                'Phone'        => $request->Phone,
                'Status'       => $request->Status,
                'birthdate'    => $request->birthdate,
                'gender'       => $request->gender,
                'photo_path'   => $request->photo_path,
                'occupation_id' => $request->occupation_id,
            ]);

            if ($user = $employee->user) {
                $user->update([
                    'name'   => $request->FirstName . ' ' . $request->LastName,
                    'email'  => $request->Email,
                    'status' => $request->Status,
                ]);
            }

            $result = [
                'item'  => new EmployeesResource($employee->load(['department', 'occupation'])),
                'meta'  => [],
                'links' => [],
            ];

            return $this->sendResponse($result, 'Employee updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Update failed.', [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return $this->sendError('Employee not found.', [], 404);
        }

        try {
            // Delete the linked user account if one exists
            if ($user = $employee->user) {
                $user->tokens()->delete();
                $user->delete();
            }

            $employee->delete();

            return $this->sendResponse([], 'Employee deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Delete operation failed.', [], 500);
        }
    }
}
