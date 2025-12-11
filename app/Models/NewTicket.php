<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewTicket extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'ticket_number',
        'department_id',
        'particular_id',
        'issue_id',
        'store_id',
        'subject',
        'description',
        'task_id',
        'field_name',
        'attachments',
        'status',
        'is_reopened',
        'priority',
        'created_by',
        'in_progress_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_reopened' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $lastTicket = NewTicket::latest()->first();

                $ticketNumber = $lastTicket ? (intval(substr($lastTicket->ticket_number, 4)) + 1) : 1;

                $ticket->ticket_number = 'TKT-' . str_pad($ticketNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function particular()
    {
        return $this->belongsTo(Particular::class);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function task()
    {
        return $this->belongsTo(ChecklistTask::class, 'task_id');
    }

    public function owners()
    {
        return $this->hasMany(NewTicketOwner::class);
    }

    public function primaryOwners()
    {
        return $this->hasMany(NewTicketOwner::class)->where('is_primary', true);
    }
    
    public function secondaryOwners()
    {
        return $this->hasMany(NewTicketOwner::class)->where('is_primary', false);
    }

    public function primaryOwner()
    {
        return $this->hasOne(NewTicketOwner::class)->where('is_primary', true);
    }

    public function histories()
    {
        return $this->hasMany(NewTicketHistory::class)->orderBy('id', 'desc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeWithTabStatus($query, string $tab)
    {
        if ($tab === 'pending') {
            return $query->where('status', self::STATUS_PENDING);
        }
        
        if ($tab === 'active') {
            return $query->where('status', self::STATUS_ACCEPTED);
        }

        if ($tab === 'inprogress') {
            return $query->where('status', self::STATUS_IN_PROGRESS);
        }

        if ($tab === 'closed') {
            return $query->where('status', self::STATUS_CLOSED);
        }

        return $query;
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPriorityLabelAttribute()
    {
        return ucfirst($this->priority);
    }
}
