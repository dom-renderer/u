<?php

use App\Http\Controllers\API\v2\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'api-maintenance'])->group(function () {
    Route::post('submit', [ApiController::class, 'submission']);
    Route::get('tasks', [ApiController::class, 'tasks']);
    Route::post('image-submissions', [ApiController::class, 'submitImages']);
    Route::post('refresh-task-list', [ApiController::class, 'refreshTaskListing']);

    Route::get('get-notifications', [ApiController::class, 'getNotifications']);
    Route::post('generate-pdf-report', [ApiController::class, 'generatePdfReport']);

    Route::get('particulars', [ApiController::class, 'particulars']);
    Route::get('issues', [ApiController::class, 'issues']);
    Route::get('users', [ApiController::class, 'users']);

    Route::post('create-ticket', [ApiController::class, 'createTicket']);
    Route::get('tickets', [ApiController::class, 'tickets']);
    Route::post('tickets/{id}/accept', [ApiController::class, 'acceptTicket']);
    Route::post('tickets/{id}/reopen', [ApiController::class, 'reopenTicket']);
    Route::post('tickets/{id}/in-progress', [ApiController::class, 'inprogressTicket']);
    Route::post('tickets/{id}/close', [ApiController::class, 'closeTicket']);
    Route::post('tickets/{id}/reply', [ApiController::class, 'replyTicket']);

    Route::get('data-web-view', [APIController::class,'dataWebView']);

    Route::get('task-status', [ApiController::class, 'taskStatus']);
    Route::get('task-progress', [ApiController::class, 'taskProgres']);
    Route::get('checklist-list', [ApiController::class, 'checklistList']);

    Route::get('document-types', [ApiController::class, 'documentTypes'])->name('document-types');
    Route::get('documents', [ApiController::class, 'documents'])->name('documents');
});