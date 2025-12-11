<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TicketitEscalation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function escalationUsers()
    {
        return $this->hasMany(TicketitEscalationUser::class, 'ticketit_escalation_id');
    }
}
