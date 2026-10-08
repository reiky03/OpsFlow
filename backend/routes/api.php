<?php

use App\Http\Controllers\Api\V1\AssetAssignmentController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\AssetHistoryController;
use App\Http\Controllers\Api\V1\AssetReturnController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\Auth\CurrentUserController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationReadAllController;
use App\Http\Controllers\Api\V1\NotificationReadController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\RolePermissionController;
use App\Http\Controllers\Api\V1\TicketActivityController;
use App\Http\Controllers\Api\V1\TicketAssignmentController;
use App\Http\Controllers\Api\V1\TicketAttachmentController;
use App\Http\Controllers\Api\V1\TicketAttachmentDownloadController;
use App\Http\Controllers\Api\V1\TicketCategoryController;
use App\Http\Controllers\Api\V1\TicketCommentController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\TicketPriorityController;
use App\Http\Controllers\Api\V1\TicketStatusController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserDeactivationController;
use App\Http\Controllers\Api\V1\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/login', LoginController::class)
            ->middleware('throttle:login')
            ->name('login');
        Route::post('/forgot-password', ForgotPasswordController::class)->middleware('throttle:5,1')->name('forgot-password');
        Route::post('/reset-password', ResetPasswordController::class)->middleware('throttle:5,1')->name('reset-password');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', LogoutController::class)->name('logout');
        });

        Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
            Route::get('/me', CurrentUserController::class)->name('me');
        });
    });

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::apiResource('tickets', TicketController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('tickets/{ticket}/assign', TicketAssignmentController::class)->name('tickets.assign');
        Route::post('tickets/{ticket}/status', TicketStatusController::class)->name('tickets.status');
        Route::post('tickets/{ticket}/priority', TicketPriorityController::class)->name('tickets.priority');
        Route::post('tickets/{ticket}/comments', TicketCommentController::class)->name('tickets.comments.store');
        Route::get('tickets/{ticket}/activities', TicketActivityController::class)->name('tickets.activities.index');
        Route::post('tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
        Route::get('tickets/{ticket}/attachments/{ticketAttachment}', TicketAttachmentDownloadController::class)->name('tickets.attachments.download');

        Route::apiResource('assets', AssetController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('assets/{asset}/assign', AssetAssignmentController::class)->name('assets.assign');
        Route::post('assets/{asset}/return', AssetReturnController::class)->name('assets.return');
        Route::get('assets/{asset}/history', AssetHistoryController::class)->name('assets.history');

        Route::apiResource('ticket-categories', TicketCategoryController::class)->only(['index', 'store', 'show', 'update']);
        Route::apiResource('departments', DepartmentController::class)->only(['index', 'store', 'show', 'update']);

        Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('users/{user}/role', UserRoleController::class)->name('users.role');
        Route::post('users/{user}/deactivate', UserDeactivationController::class)->name('users.deactivate');
        Route::get('roles', RoleController::class)->name('roles.index');
        Route::get('permissions', PermissionController::class)->name('permissions.index');
        Route::put('roles/{role}/permissions', RolePermissionController::class)->name('roles.permissions.update');

        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('notifications', NotificationController::class)->name('notifications.index');
        Route::post('notifications/read-all', NotificationReadAllController::class)->name('notifications.read-all');
        Route::post('notifications/{notification}/read', NotificationReadController::class)->name('notifications.read');
        Route::get('audit-logs', AuditLogController::class)->name('audit-logs.index');
    });
});
