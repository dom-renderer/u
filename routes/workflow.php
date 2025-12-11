<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'permission']], function() {

    Route::resource('workflow-checklists', \App\Http\Controllers\WorkflowChecklistController::class);
    Route::match(['GET', 'PUT'],'workflow-duplicate-checklist/{id}', [\App\Http\Controllers\WorkflowChecklistController::class, 'duplicate'])->name('workflow-duplicate-checklist');

    Route::resource('workflow-templates', \App\Http\Controllers\WorkflowTemplateController::class);
    Route::get('workflow-templates/{id}/tree', [\App\Http\Controllers\WorkflowTemplateController::class, 'treeView'])->name('workflow-templates.tree');
    Route::get('workflow-templates/{id}/tree-data', [\App\Http\Controllers\WorkflowTemplateController::class, 'treeData'])->name('workflow-templates.tree-data');
    
    Route::resource('workflow-assignments', \App\Http\Controllers\WorkflowAssignmentController::class);
    Route::get('workflow-assignments/{id}/tree', [\App\Http\Controllers\WorkflowAssignmentController::class, 'treeView'])->name('workflow-assignments.tree');
    Route::get('workflow-assignments/{id}/tree-data', [\App\Http\Controllers\WorkflowAssignmentController::class, 'treeData'])->name('workflow-assignments.tree-data');
    Route::get('workflow-assignments/load-template/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'loadTemplate'])->name('workflow-assignments.load-template');

});