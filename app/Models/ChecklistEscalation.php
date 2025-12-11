<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChecklistEscalation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'templates' => 'object'
    ];

    public function scopeNoncompletion($query) {
        return $query->where('type', 0);
    }

    public function scopeCompletion($query) {
        return $query->where('type', 1);
    }
}
