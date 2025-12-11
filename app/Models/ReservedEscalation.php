<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ReservedEscalation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function escalation() {
        return $this->belongsTo(ChecklistEscalation::class, 'escalation_id');
    }
}
