<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChecklistSchedulingExtra extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent() {
        return $this->belongsTo(ChecklistScheduling::class, 'checklist_scheduling_id');
    }

    public function store() {
        return $this->belongsTo(Store::class, 'branch_id');
    }

    public function actstore() {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function tasks() {
        return $this->hasMany(ChecklistTask::class, 'checklist_scheduling_id');
    }

    public function department() {
        return $this->belongsTo(Department::class, 'branch_id');
    }
}
