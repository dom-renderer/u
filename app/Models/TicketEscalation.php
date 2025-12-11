<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketEscalation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'particular_id',
        'issue_id',
        'level1_hours',
        'level1_users',
        'level1_notifications',
        'level2_hours',
        'level2_users',
        'level2_notifications',
        'created_by',
        'pending_level1_hours',
        'pending_level1_users',
        'pending_level1_notifications',
        'pending_level2_hours',
        'pending_level2_users',
        'pending_level2_notifications',
    ];

    protected $casts = [
        'level1_users' => 'array',
        'level1_notifications' => 'array',
        'level2_users' => 'array',
        'level2_notifications' => 'array',
        'pending_level1_users' => 'array',
        'pending_level1_notifications' => 'array',
        'pending_level2_users' => 'array',
        'pending_level2_notifications' => 'array',
    ];

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
}
