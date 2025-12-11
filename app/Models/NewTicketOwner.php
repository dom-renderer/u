<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewTicketOwner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'new_ticket_id',
        'owner_id',
        'assigned_by',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(NewTicket::class, 'new_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by')->withTrashed();
    }
}
