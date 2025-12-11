<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PdfGenerationLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function task() {
        return $this->belongsTo(ChecklistTask::class, 'task_id');
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
