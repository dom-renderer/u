<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TicketitEscalationUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function escalation()
    {
        return $this->belongsTo(TicketitEscalation::class, 'ticketit_escalation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }
}
