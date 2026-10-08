<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Audit\ListAuditLogRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ListAuditLogRequest $request): JsonResponse
    {
        $entityTypes = ['Asset' => Asset::class, 'Ticket' => Ticket::class, 'User' => User::class];
        $query = AuditLog::query()->with('user')
            ->when($request->filled('action'), fn (Builder $query) => $query->where('action', $request->string('action')))
            ->when($request->filled('user_id'), fn (Builder $query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('entity_type'), fn (Builder $query) => $query->where('entity_type', $entityTypes[$request->string('entity_type')->toString()] ?? '__invalid__'))
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest();
        $paginator = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => AuditLogResource::collection($paginator->getCollection()),
            'meta' => ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
        ]);
    }
}
