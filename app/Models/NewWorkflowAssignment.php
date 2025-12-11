<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewWorkflowAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'sections' => 'array',
        'start_from' => 'datetime'
    ];

    public function children() {
        return $this->hasMany(NewWorkflowAssignmentItem::class, 'new_workflow_assignment_id');
    }

    public function template() {
        return $this->belongsTo(NewWorkflowTemplate::class, 'new_workflow_template_id');
    }
}

