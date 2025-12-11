<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SchedulingImport extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $guarded = [];

        protected $casts = [
        'response' => 'object'
    ];

    public function checklist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id');
    }

    public function uploader() {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function scopeScheduling($query) {
        return $query->where('type', 0);
    }

    public static function scopeUsers($query) {
        return $query->where('type', 1);
    }
}
