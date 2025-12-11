<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DynamicForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'schema' => 'object'
    ];

    public static function scopeInspection($query) {
        return $query->where('type', 0);
    }

    public static function scopeWorkflow($query) {
        return $query->where('type', 1);
    }

    public function escalations() {
        return $this->hasMany(ChecklistEscalation::class, 'checklist_id', 'id')->orderBy('level');
    }

    public function store() {
        return $this->belongsTo(Store::class, 'branch_id', 'id');
    }

    public function department() {
        return $this->belongsTo(Department::class, 'branch_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function presetemplates() {
        return $this->hasMany(TemplatePresetNotification::class, 'checklist_id');
    }
}