<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\CreateTicket;
use App\Actions\Tickets\UpdateTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\ListTicketRequest;
use App\Http\Requests\Api\V1\Ticket\StoreTicketRequest;
use App\Http\Requests\Api\V1\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\PermissionName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListTicketRequest $request): JsonResponse
    {
        $user = $request->user();
        $query = Ticket::query()->with(['category', 'reporter', 'assignedTechnician', 'asset']);

        if (! $user->hasPermissionTo(PermissionName::TicketViewAll)) {
            $query->where(function (Builder $query) use ($user): void {
                if ($user->hasPermissionTo(PermissionName::TicketViewOwn)) {
                    $query->orWhere('reporter_id', $user->id);
                }
                if ($user->hasPermissionTo(PermissionName::TicketViewAssigned)) {
                    $query->orWhere('assigned_technician_id', $user->id);
                }
                if ($user->hasPermissionTo(PermissionName::TicketViewUnassigned)) {
                    $query->orWhereNull('assigned_technician_id');
                }
            });
        }

        $query
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('ticket_number', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('title', "%{$search}%", caseSensitive: false)
                        ->orWhereHas('reporter', fn (Builder $query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                        ->orWhereHas('assignedTechnician', fn (Builder $query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn (Builder $query) => $query->where('priority', $request->string('priority')))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('technician_id'), fn (Builder $query) => $query->where('assigned_technician_id', $request->integer('technician_id')))
            ->when($request->filled('reporter_id'), fn (Builder $query) => $query->where('reporter_id', $request->integer('reporter_id')))
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->date('date_to')));

        $sortBy = $request->validated('sort_by', 'created_at');
        $sortDirection = $request->validated('sort_direction', 'desc');
        $paginator = $query->orderBy($sortBy, $sortDirection)
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => TicketResource::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, CreateTicket $createTicket): JsonResponse
    {
        $ticket = $createTicket->handle($request->user(), $request->validated());
        $ticket->load(['category', 'reporter', 'assignedTechnician', 'asset']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully.',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);
        $canViewInternal = $request->user()->can('viewInternalNotes', $ticket);
        $ticket->load([
            'category',
            'reporter',
            'assignedTechnician',
            'asset',
            'comments' => fn ($query) => $query
                ->when(! $canViewInternal, fn ($query) => $query->where('is_internal', false))
                ->with(['user', 'attachments'])
                ->oldest(),
            'attachments' => fn ($query) => $query->with('uploader')->whereNull('ticket_comment_id')->oldest(),
            'activities' => fn ($query) => $query->with('actor')->oldest(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket retrieved successfully.',
            'data' => new TicketResource($ticket),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdateTicket $updateTicket): JsonResponse
    {
        $ticket = $updateTicket->handle($ticket, $request->user(), $request->validated());
        $ticket->load(['category', 'reporter', 'assignedTechnician', 'asset']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully.',
            'data' => new TicketResource($ticket),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        abort(405, 'Tickets cannot be deleted.');
    }
}
