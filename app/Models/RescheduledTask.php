<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RescheduledTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function task() {
        return $this->belongsTo(ChecklistTask::class, 'task_id');
    }
}
