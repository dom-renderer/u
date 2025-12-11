<?php

use App\Http\Controllers\API\ApiController;
use Illuminate\Support\Facades\Route;

Route::post('login', [APIController::class, 'login']);
Route::post('forgot-password', [APIController::class, 'forgotPassword']);
Route::post('change-password', [APIController::class, 'changePassword']);
Route::get('get-urls', [APIController::class,'getUrls']);
Route::get('get-maintenance-status', [APIController::class,'getMaintenanceStatus']);

Route::middleware(['auth:api', 'api-maintenance'])->group(function () {
    Route::get('stores', [APIController::class, 'stores']);
    Route::get('departments', [APIController::class, 'departments']);
    Route::get('checklists', [APIController::class, 'checklists']);

    Route::post('device-token', [APIController::class, 'deviceToken']);
    Route::post('remove-device-token', [APIController::class, 'removeDeviceToken']);
    Route::get('tasks', [APIController::class, 'tasks']);
    Route::get('dashboard-statistics', [APIController::class, 'dashboard']);
    Route::post('submit', [APIController::class, 'submission']);
    Route::get('statistics-of-task', [APIController::class, 'taskVariables']);

    Route::post('approve-decline', [APIController::class, 'approveDecline']);
    Route::get('list-redo-action-tasks', [APIController::class, 'redoActionTasks']);
    Route::get('get-redo-actions', [APIController::class, 'getRedoActions']);
    Route::post('submit-redo', [APIController::class, 'submitRedo']);

    Route::get('reassignment-tasks', [APIController::class, 'reassignmentTasks']);

    Route::post('submission-duration', [APIController::class, 'submissionDurationCount']);
    Route::post('reschedule-task', [APIController::class, 'rescheduleTask']);
    Route::get('reschedule-task-list', [APIController::class, 'rescheduleTaskListing']);
    Route::post('reschedule-task-reschedule', [APIController::class, 'rescheduleTaskReschedule']);

    Route::get('get-assigned-task-in-month-view', [APIController::class, 'taskMonthView']);

    Route::get('tasks-log', [APIController::class, 'logs']);

    Route::get('topics', [ApiController::class, 'topics']);
    Route::get('tags', [ApiController::class, 'tags']);
    Route::get('contents', [ApiController::class, 'content']);
    Route::post('view-count', [ApiController::class, 'viewCount']);

    Route::post('add-ticket', [ApiController::class, 'addTicket']);

    Route::get('priorities', [ApiController::class, 'priorities']);
    Route::get('statuses', [ApiController::class, 'statuses']);
    Route::get('get-tickets', [ApiController::class, 'getTickets']);
    Route::post('comment-on-ticket', [ApiController::class, 'commentOnTicket']);

    Route::post('change-ticket-status', [ApiController::class, 'changeTicketStatus']);

    Route::get('home-menus', [ApiController::class, 'homeMenus']);
    
    Route::post('create-task', [APIController::class, 'createTask']);

    Route::post('add-task-start-timestamp', [APIController::class, 'addTaskStartTimestamp']);
    Route::post('submit-task-time-multiple-log', [APIController::class, 'addTaskStartTimestampMultiple']);

    Route::post('update-ticket-data', [APIController::class,'alterTicket']);
});