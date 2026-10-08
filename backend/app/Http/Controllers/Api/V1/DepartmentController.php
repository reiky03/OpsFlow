<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\StoreDepartmentRequest;
use App\Http\Requests\Api\V1\MasterData\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DepartmentResource::collection(Department::query()->orderBy('name')->get()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::query()->create($request->validated());

        return response()->json(['success' => true, 'message' => 'Department created successfully.', 'data' => new DepartmentResource($department)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): JsonResponse
    {
        return response()->json(['success' => true, 'data' => new DepartmentResource($department)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Department updated successfully.', 'data' => new DepartmentResource($department)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        abort(405, 'Departments cannot be deleted through the API.');
    }
}
