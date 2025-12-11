<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewTicketHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'new_ticket_id',
        'description',
        'data',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'data' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(NewTicket::class, 'new_ticket_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function getTypeAttribute()
    {
        return $this->data['type'] ?? null;
    }

    public function getMetaAttribute()
    {
        return $this->data ?? [];
    }
}
