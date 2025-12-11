<?php

use App\Http\Controllers\StoreMultiChecklistImportController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\MultiChecklistImportController;
use App\Http\Controllers\ChecklistSchedulingController;
use App\Http\Controllers\SchedulingImportController;
use App\Http\Controllers\RescheduledTaskController;
use App\Http\Controllers\CorporateOfficeController;
use App\Http\Controllers\DocumentsUploadController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\StoreCategoryController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DoMDashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ParticularController;
use App\Http\Controllers\StoreTypeController;
use App\Http\Controllers\ModelTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use App\Helpers\Helper;

Route::group(['middleware' => ['guest']], function() {
    Route::get('/login', [LoginController::class , 'show'])->name('login.show');
    Route::post('/login', [LoginController::class , 'login'])->name('login.perform');
    Route::get('forget-password', [ForgotPasswordController::class , 'showLinkRequestForm'])->name('password.request');
    Route::post('forget-password', [ForgotPasswordController::class , 'sendResetLinkEmail'])->name('password.email'); 
    Route::get('reset-password/{token}', [ForgotPasswordController::class , 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class , 'reset'])->name('password.update');
});

Route::get('logout', [DashboardController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth', 'permission']], function() {

    Route::any('/', [DashboardController::class, 'index'])->name('dashboard')->withoutMiddleware(['permission']);
    Route::get('/inspection-dashboard', [DashboardController::class, 'flaggedItemsView'])->name('flagged-items-dashboard');
    Route::get( '/document-dashboard', [ DashboardController::class, 'documentDashboard' ] )->name( 'document-dashboard' );
    Route::post( '/document-dashboard/remind-later/{id}', [ DashboardController::class, 'documentRemindLater' ] )->name( 'document-dashboard-remindLater' );
    Route::get('monthly-report-dom-checklists', [MonthlyReportController::class, 'index'])->name('monthly-report-dom-checklists');
    Route::get('monthly-report-dom-checklists-export', [MonthlyReportController::class, 'export'])->name('monthly-report-dom-checklists-export');

    Route::get('dom-dashboard', [DoMDashboardController::class, 'index'])->name('dom-dashboard');

    /* Inspection Management */
    Route::resource('roles', RolesController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('stores', StoreController::class);
    Route::resource( 'store-categories', StoreCategoryController::class );
    Route::resource('store-types', StoreTypeController::class);
    Route::resource('model-types', ModelTypeController::class);
    Route::resource('corporate-office', CorporateOfficeController::class);
    Route::resource('checklists', ChecklistController::class);
    Route::resource('checklist-scheduling', ChecklistSchedulingController::class);
    Route::resource('scheduled-tasks', ScheduledTaskController::class);
    /* Inspection Management */

    /* Learning Management System */
    Route::resource('categories', TopicController::class)->names('topics');
    Route::resource('contents', ContentController::class);

    Route::post('topics-select2', [TopicController::class, 'categorySelect2'])->name('topics-select2')->withoutMiddleware(['permission']);
    Route::post('get-sub-cat-count', [TopicController::class, 'getSubCatCount'])->name('get-sub-cat-count')->withoutMiddleware(['permission']);
    Route::post('topics-enable-disable/{id}', [TopicController::class, 'enableDisable'])->name('topics.enable-disable')->withoutMiddleware(['permission']);
    Route::post('sort-categories', [TopicController::class, 'sort'])->name('sort-categories')->withoutMiddleware(['permission']);

    Route::post('contents-upload-attachment', [ContentController::class, 'uploadAttachment'])->name('contents.upload-attachment')->withoutMiddleware(['permission']);
    Route::delete('contents/delete-attachment/{id}', [ContentController::class, 'deleteAttachment'])->name('contents.delete-attachment')->withoutMiddleware(['permission']);
    Route::get('content-analytics', [ContentController::class, 'analytics'])->name('content-analytics');
    Route::post('sort-contents', [ContentController::class, 'sort'])->name('sort-contents')->withoutMiddleware(['permission']);    
    /* Learning Management System */

    Route::resource('notification-templates', NotificationTemplateController::class);

    Route::put('scheduled-tasks-change-date-time/{id}', [ScheduledTaskController::class, 'changeStartEndTime'])->name('scheduled-tasks-change-date-time');
    Route::post('bulk-delete-scheduled-tasks', [ScheduledTaskController::class, 'bulkDelete'])->name('scheduled-tasks.bulk-delete');
    Route::post('reschedule-task/{id}', [ScheduledTaskController::class, 'reschedule'])->name('reschedule-task');
    Route::post('cancel-task/{id}', [ScheduledTaskController::class, 'cancel'])->name('cancel-task');

    Route::get('reassignments', [ScheduledTaskController::class, 'reassignmentList'])->name('reassignments.index');
    Route::get('reassignments-show/{id}', [ScheduledTaskController::class, 'reassignmentView'])->name('reassignments.show');

    Route::match(['GET', 'POST'], 'import-schedule/{id?}', [ChecklistSchedulingController::class, 'importScheduling'])->name('import.scheduling');
    Route::match(['GET', 'POST'], 'multi-checklist-import', [MultiChecklistImportController::class, 'import'])->name('multi-checklist-import');
    Route::match(['GET', 'POST'], 'store-multi-checklist-import', [StoreMultiChecklistImportController::class, 'import'])->name('store-multi-checklist-import');

    Route::group(['prefix' => 'users'], function() {
        Route::get('/', [UsersController::class, 'index'])->name('users.index');
        Route::get('/create', [UsersController::class, 'create'])->name('users.create');
        Route::post('/create', [UsersController::class, 'store'])->name('users.store');
        Route::get('/{user}/show', [UsersController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::patch('/{user}/update', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/{user}/delete', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::delete('/{user}/restore', [UsersController::class, 'restore'])->name('users.restore');
        Route::post('users-import', [UsersController::class, 'import'])->name('users.import');
        Route::get('users-export', [UsersController::class, 'export'])->name('users.export');
        Route::delete('/{user}/remove', [UsersController::class, 'remove'])->name('users.remove');
        Route::get('/{user}/showDeleted', [UsersController::class, 'showDeleted'])->name('users.show.deleted');
        Route::get('/getUsers', [UsersController::class, 'getUsers'])->name('datatable.users')->withoutMiddleware(['permission']);
        Route::get('/getArchiveUsers', [UsersController::class, 'getArchiveUsers'])->name('datatable.users.archive')->withoutMiddleware(['permission']);
    });

    Route::get('reschedules', [RescheduledTaskController::class, 'index'])->name('reschedules');
    Route::get('notification-center', [NotificationController::class, 'index'])->name('notification-center');

    Route::resource( 'documents', DocumentsController::class );
    Route::resource( 'document-upload', DocumentsUploadController::class );
    Route::match(['GET', 'POST'], 'document-upload-import', [DocumentsUploadController::class, 'import'])->name('document-upload.import');

    Route::resource( 'particulars', ParticularController::class );
    Route::resource( 'issues', IssueController::class );
    Route::get('tickets/export', [TicketController::class, 'export'])->name('ticket-management.export');
    Route::get('tickets/{encryptedTicket}/reopen', [TicketController::class, 'edit'])->name('ticket-management.reopen');
    Route::post('tickets/{encryptedTicket}/accept', [TicketController::class, 'accept'])->name('ticket-management.accept');
    Route::match(['GET','POST'],'tickets/{encryptedTicket}/assign-users', [TicketController::class, 'assignUsers'])->name('ticket-management.assign-users')->withoutMiddleware(['permission']);
    Route::resource('ticket-management', TicketController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->parameters(['tickets' => 'encryptedTicket']);
    Route::get('ticket-escalations', [TicketController::class, 'escalations'])->name('ticket-escalations.index');
    Route::get('ticket-escalations/create', [TicketController::class, 'createEscalation'])->name('ticket-escalations.create');
    Route::get('ticket-escalations/{id}/show', [TicketController::class, 'showEscalation'])->name('ticket-escalations.view');
    Route::post('ticket-escalations', [TicketController::class, 'storeEscalation'])->name('ticket-escalations.store');
    Route::get('ticket-escalations/{id}/edit', [TicketController::class, 'editEscalation'])->name('ticket-escalations.edit');
    Route::match(['PUT', 'PATCH'],'ticket-escalations/{id}', [TicketController::class, 'updateEscalation'])->name('ticket-escalations.update');
    Route::delete('ticket-escalations/{id}', [TicketController::class, 'destroyEscalation'])->name('ticket-escalations.destroy');
    
    Route::withoutMiddleware('permission')->group(function() {
        Route::any('task-versioning', [ScheduledTaskController::class, 'taskVersioning'])->name('task-versioning');
        Route::post('checklists/reorder', [ChecklistController::class, 'reorder'])->name('checklists.reorder');
        Route::post('checklist-dates-list', [ScheduledTaskController::class, 'checklistDatesList'])->name('checklist-dates-list');
        Route::post('state-list', [StoreController::class, 'stateLists'])->name('state-list');
        Route::post('city-list', [StoreController::class, 'cityLists'])->name('city-list');
        Route::post('users-list', [UsersController::class, 'getAllUsers'])->name('users-list');
        Route::post('content-list', [ContentController::class, 'getAllContent'])->name('content-list');
        Route::post('tag-select2', [TopicController::class, 'getAllTags'])->name('tag-select2');
        Route::post('notification-template-list', [NotificationTemplateController::class, 'select2List'])->name('notification-template-list');
        Route::post('departments-list', [DepartmentController::class, 'select2List'])->name('departments-list');
        Route::post('stores-list', [StoreController::class, 'select2List'])->name('stores-list');
        Route::post('corporate-offices-list', [CorporateOfficeController::class, 'select2List'])->name('corporate-offices-list');
        Route::post('checklists-list', [ChecklistController::class, 'select2List'])->name('checklists-list');
        Route::post('documents-list', [DocumentsController::class, 'select2List'])->name('documents-list');
        Route::get('checklists-render/{id}', [ChecklistController::class, 'renderForViewOnly'])->name('checklists.render');
        Route::get('dashboard-filter', [DashboardController::class, 'filter'])->name('dashboard-filter');
        Route::match(['GET', 'PUT'],'duplicate-checklist/{id}', [ChecklistController::class, 'duplicate'])->name('duplicate-checklist');
        Route::get('task-log/{id}', [Helper::class, 'taskLog'])->name('task-log');
        Route::get('view-flagged-items', [DoMDashboardController::class, 'detail'])->name('view-flagged-items');
        Route::post('verify-each-fields/{id}', [ScheduledTaskController::class, 'verifyEachFields'])->name('verify-each-fields');
        Route::get('task-status-change', [ScheduledTaskController::class, 'changeStatus'])->name('task-status-change');
        Route::get('truthy-falsy', [ScheduledTaskController::class, 'truthyFalsyFields'])->name('truthy-falsy');

        Route::get('dom-dashboard-2', [DoMDashboardController::class, 'index2'])->name('dom-dashboard-2');
        Route::get('dom-dashboard-2-specific-store', [DoMDashboardController::class, 'index3'])->name('dom-dashboard-2-specific-store');
        Route::get('view-flagged-items-2', [DoMDashboardController::class, 'detail2'])->name('view-flagged-items-2');

        Route::get('compare-checklist', [ScheduledTaskController::class, 'compare'])->name('compare-checklist');
        Route::get('fetch-task-data-to-compare', [ScheduledTaskController::class, 'fetchTaskDataToCompare'])->name('fetch-task-data-to-compare');
        Route::post('export-comparison', [ScheduledTaskController::class, 'exportComparison'])->name('export-comparison');

        Route::post('submit-reschedule-response/{id}', [RescheduledTaskController::class, 'submitRescheduleResponse'])->name('submit-reschedule-response');
        Route::post('contents.enable-disable/{id}', [ContentController::class, 'enableDisable'])->name('contents.enable-disable');

        Route::post('scheduled-task-list', [ScheduledTaskController::class, 'select2List'])->name('scheduled-task-list');
        Route::get('export-tickets', [DashboardController::class, 'pdfTickets'])->name('export-tickets');
        Route::get('export-flagged-items-export', [DashboardController::class, 'pdfFItems'])->name('export-flagged-items-export');

        Route::post('particulars-list', [ParticularController::class, 'select2List'])->name('particulars-list');
        Route::post('issues-list', [IssueController::class, 'select2List'])->name('issues-list');
    });
});

Route::post('import-stores', [StoreController::class, 'importStores'])->name('import-stores');
Route::get('export-stores', [StoreController::class, 'exportStores'])->name('export-stores');
Route::get('imported-schedulings-history', [SchedulingImportController::class, 'index'])->name('imported-schedulings-history');
Route::post('imported-schedulings-bulk-delete', [SchedulingImportController::class, 'bulkDelete'])->name('imported-schedulings-bulk-delete');

Route::match(['GET', 'POST'], 'checklists-submission/{id}', [ScheduledTaskController::class, 'submission'])->name('checklists-submission');
Route::get('checklists-submission-view/{id}', [ScheduledTaskController::class, 'submissionView'])->name('checklists-submission-view');
Route::get('checklists-submission-view-for-maker/{id}', [ScheduledTaskController::class, 'submissionViewForMaker'])->name('checklists-submission-view-for-maker');
Route::get('checklists-submission-view-for-checker/{id}', [ScheduledTaskController::class, 'submissionViewForChecker'])->name('checklists-submission-view-for-checker');
Route::get('checklists-submission-comparison/{id}', [ScheduledTaskController::class, 'sideBySideComparison'])->name('checklists-submission-comparison');
Route::get('submission-response', function () {})->name('submission-response');


Route::get('task-export-excel/{id}', [DashboardController::class, 'exportExcel'])->name('task-export-excel');
Route::get('task-export-pdf/{id}', [DashboardController::class, 'exportPdf'])->name('task-export-pdf');
Route::get('task-export-compressed-pdf/{id}', [DashboardController::class, 'exportCompressedPdf'])->name('task-export-compressed-pdf');
Route::get('test-report/{id}', [DashboardController::class, 'testPdf'])->name('test-report');


Route::get('get-ticket-listing', [TicketsController::class, 'getListing'])->name('get-ticket-listing');
Route::post('checklist-scheduling-bulk-delete', [ChecklistSchedulingController::class, 'bulkDelete'])->name('checklist-scheduling.bulk-delete');


Route::get('/settings/edit', [SettingController::class, 'edit'])->name('settings.edit');
Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
Route::view('tmp-mail', 'emails.ticket-watchers')->name('tmp-mail');

Route::get('add-watermark-on-image', [SettingController::class, 'addWatermark'])->name('add-watermark-on-image');

Route::get('give-permission-to-storage', [SettingController::class, 'givePermission'])->name('give-permission-to-storage');