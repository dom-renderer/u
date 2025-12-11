<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewWorkflowAssignmentItem extends Model
{
    use HasFactory, softDeletes;

    protected $guarded = [];

    protected $casts = [
        'dependency_steps' => 'array',
        'is_entry_point' => 'boolean'
    ];

    public function parent() {
        return $this->belongsTo(NewWorkflowAssignment::class, 'new_workflow_assignment_id');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function checklist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function makerEscalationUser() {
        return $this->belongsTo(User::class, 'maker_escalation_user_id');
    }

    public function checker() {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function checkerEscalationUser() {
        return $this->belongsTo(User::class, 'checker_escalation_user_id');
    }

    public function makerEscalationEmailNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_escalation_email_notification');
    }

    public function makerEscalationPushNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_escalation_push_notification');
    }

    public function checkerEscalationEmailNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'checker_escalation_email_notification');
    }

    public function checkerEscalationPushNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'checker_escalation_push_notification');
    }
}

