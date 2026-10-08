<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\StoreTicketCategoryRequest;
use App\Http\Requests\Api\V1\MasterData\UpdateTicketCategoryRequest;
use App\Http\Resources\TicketCategoryResource;
use App\Models\TicketCategory;
use Illuminate\Http\JsonResponse;

class TicketCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TicketCategoryResource::collection(TicketCategory::query()->orderBy('name')->get()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketCategoryRequest $request): JsonResponse
    {
        $category = TicketCategory::query()->create($request->validated());

        return response()->json(['success' => true, 'message' => 'Ticket category created successfully.', 'data' => new TicketCategoryResource($category)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TicketCategory $ticketCategory): JsonResponse
    {
        return response()->json(['success' => true, 'data' => new TicketCategoryResource($ticketCategory)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketCategoryRequest $request, TicketCategory $ticketCategory): JsonResponse
    {
        $ticketCategory->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Ticket category updated successfully.', 'data' => new TicketCategoryResource($ticketCategory)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketCategory $ticketCategory): JsonResponse
    {
        abort(405, 'Ticket categories cannot be deleted through the API.');
    }
}
