<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'permission']], function() {

    Route::resource('workflow-checklists', \App\Http\Controllers\WorkflowChecklistController::class);
    Route::resource('workflow-templates', \App\Http\Controllers\WorkflowTemplateController::class);
    Route::get('workflow-templates/{id}/tree', [\App\Http\Controllers\WorkflowTemplateController::class, 'treeView'])->name('workflow-templates.tree');
    Route::get('workflow-templates/{id}/tree-data', [\App\Http\Controllers\WorkflowTemplateController::class, 'treeData'])->name('workflow-templates.tree-data');
    Route::match(['GET', 'PUT'],'workflow-duplicate-checklist/{id}', [\App\Http\Controllers\WorkflowChecklistController::class, 'duplicate'])->name('workflow-duplicate-checklist');

});