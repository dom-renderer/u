<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ContentEllipse;
use App\Traits\Purifiable;
use Jenssegers\Date\Date;

class Ticket extends Model
{
    use ContentEllipse;
    use SoftDeletes;
    use Purifiable;

    protected $table = 'ticketit';
    protected $dates = ['completed_at'];

    /**
     * List of completed tickets.
     *
     * @return bool
     */
    public function hasComments()
    {
        return (bool) count($this->comments);
    }

    public function isComplete()
    {
        return (bool) $this->completed_at;
    }

    /**
     * List of completed tickets.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * List of active tickets.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Get Ticket status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id');
    }

    /**
     * Get Ticket priority.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority()
    {
        return $this->belongsTo('App\Models\Priority', 'priority_id');
    }

    /**
     * Get Ticket category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    /**
     * Get Ticket owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Agent', 'user_id');
    }

    /**
     * Get Ticket agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo('App\Models\Agent', 'agent_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }

    public function agents()
    {
        return $this->belongsTo('App\Models\TicketMember', 'user_id');
    }

    /**
     * Get Ticket comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'ticket_id');
    }

    public function atchmnts()
    {
        return $this->hasMany('App\Models\TicketAttachment', 'ticket_id');
    }

        public function histories()
    {
        return $this->hasMany('App\Models\TicketHistory', 'ticket_id');
    }

//    /**
    //     * Get Ticket audits
    //     *
    //     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    //     */
    //    public function audits()
    //    {
    //        return $this->hasMany('App\Models\Audit', 'ticket_id');
    //    }
    //

    /**
     * @see Illuminate/Database/Eloquent/Model::asDateTime
     */
    public function freshTimestamp()
    {
        return new Date();
    }

    /**
     * @see Illuminate/Database/Eloquent/Model::asDateTime
     */
    protected function asDateTime($value)
    {
        if (is_numeric($value)) {
            return Date::createFromTimestamp($value);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return Date::createFromFormat('Y-m-d', $value)->startOfDay();
        } elseif (!$value instanceof \DateTimeInterface) {
            $format = $this->getDateFormat();

            return Date::createFromFormat($format, $value);
        }

        return Date::instance($value);
    }

    /**
     * Get all user tickets.
     *
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeUserTickets($query, $id)
    {
        return $query->where('user_id', $id);
    }

    public function allagent() {
        return $this->hasMany(TicketMember::class, 'ticket_id');
    }

    /**
     * Get all agent tickets.
     *
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeAgentTickets($query, $id)
    {
        return $query->where('agent_id', $id);
    }

    public function scopeAgentsTickets($query, $id)
    {
        return $query->whereHas('allagent', function ($builder) use ($id) {
            $builder->where('user_id', $id);
        });
    }

    /**
     * Get all agent tickets.
     *
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeAgentUserTickets($query, $id)
    {
        return $query->where(function ($subquery) use ($id) {
            $subquery->where('agent_id', $id)->orWhere('user_id', $id);
        });
    }

    /**
     * Sets the agent with the lowest tickets assigned in specific category.
     *
     * @return Ticket
     */
    public function autoSelectAgent()
    {
        $cat_id = $this->category_id;
        $agents = Category::find($cat_id)->agents()->with(['agentOpenTickets' => function ($query) {
            $query->addSelect(['id', 'agent_id']);
        }])->get();
        $count = 0;
        $lowest_tickets = 1000000;
        // If no agent selected, select the admin
        $first_admin = Agent::admins()->first();
        if(!empty($first_admin)) {
            $selected_agent_id = $first_admin->id;
            foreach ($agents as $agent) {
                if ($count == 0) {
                    $lowest_tickets = $agent->agentOpenTickets->count();
                    $selected_agent_id = $agent->id;
                } else {
                    $tickets_count = $agent->agentOpenTickets->count();
                    if ($tickets_count < $lowest_tickets) {
                        $lowest_tickets = $tickets_count;
                        $selected_agent_id = $agent->id;
                    }
                }
                $count++;
            }
            $this->agent_id = $selected_agent_id;

            return $this;
        } else {
            throw new \Exception("Any admin is not exist.");
        }
    }

    public function latest_comments()
    {
        return $this->hasOne('App\Models\Comment', 'ticket_id')->latest();
    }

    public function allcomments()
    {
        return $this->hasMany('App\Models\Comment', 'ticket_id')->latest();
    }

    public function tsk()
    {
        return $this->belongsTo('App\Models\ChecklistTask', 'task_id');
    }

    public function escalationExecutions()
    {
        return $this->hasMany('App\Models\TicketitEscalationExecution', 'ticket_id');
    }

    public function latestEscalation()
    {
        return $this->hasOne('App\Models\TicketitEscalationExecution', 'ticket_id')
            ->join('ticketit_escalations', 'ticketit_escalation_executions.ticketit_escalation_id', '=', 'ticketit_escalations.id')
            ->orderBy('ticketit_escalation_executions.created_at', 'desc')
            ->select('ticketit_escalation_executions.*', 'ticketit_escalations.escalation_level');
    }
}
