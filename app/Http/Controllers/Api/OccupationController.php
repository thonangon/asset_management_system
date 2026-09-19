<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Occupation;
use Illuminate\Http\Request;
use App\Http\Resources\OccupationResource;
use Illuminate\Support\Facades\Validator;

class OccupationController extends Controller
{
    public function index()
    {
        try {
            $occupations = Occupation::all();
            if ($occupations->isEmpty()) throw_msg('Occupation not found', 404);
            $result = [
                'item' => [],
                'items' => OccupationResource::collection($occupations),
                'meta' => [],
                'links' => []
            ];
            return $this->sendResponse($result, 'Occupation retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        if ($validate->fails()) {
            return $this->sendError($validate->errors()->first(), [], 422);
        }
        try {
            $occupation = Occupation::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            $result = [
                'item' => new OccupationResource($occupation),
                'items' => [],
                'meta' => [],
                'links' => []
            ];
            return $this->sendResponse($result, 'Occupation created successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Occupation $occupation)
    {
        return $this->sendResponse(new OccupationResource($occupation), 'Occupation retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Occupation $occupation)
    {
        $validate = Validator::make(request()->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        if ($validate->fails()) {
            return $this->sendError($validate->errors()->first(), [], 422);
        }
        try {
            $occupation->update([
                'name' => request()->name,
                'description' => request()->description,
            ]);
            $result = [
                'item' => new OccupationResource($occupation),
                'items' => [],
                'meta' => [],
                'links' => []
            ];
            return $this->sendResponse($result, 'Occupation updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Occupation $occupation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Occupation $occupation)
    {
        try {
            $occupation->delete();
            return $this->sendResponse([], 'Occupation deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
}
