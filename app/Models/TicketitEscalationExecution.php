<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TicketitEscalationExecution extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function escalation()
    {
        return $this->belongsTo(TicketitEscalation::class, 'ticketit_escalation_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
