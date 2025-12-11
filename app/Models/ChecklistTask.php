<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class ChecklistTask extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $auditInclude = [
        'completion_date',
        'status',
        'data',
        'started_at',
        'extra_info'
    ];

    protected $casts = [
        'data' => 'object',
        'form' => 'object',
        'extra_info' => 'object'
    ];

    public function parent() {
        return $this->belongsTo(ChecklistSchedulingExtra::class, 'checklist_scheduling_id');
    }

    public function scopePending($query) {
        return $query->where('status', 0);
    }

    public function scopeInprogress($query) {
        return $query->where('status', 1);
    }

    public function scopeCompleted($query) {
        return $query->where('status', 2);
    }

    public static function scopeScheduling($query) {
        return $query->where('type', 0);
    }

    public static function scopeWorkflow($query) {
        return $query->where('type', 1);
    }

    public function clist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id');
    }

    public function redos() {
        return $this->hasMany(RedoAction::class, 'task_id');
    }

    public function submissionentries() {
        return $this->hasMany(SubmissionTime::class, 'task_id')->orderBy('id', 'DESC');
    }

    public function restasks() {
        return $this->hasMany(RescheduledTask::class, 'task_id')->orderBy('id', 'DESC');
    }
}