<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Particular extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function issues() {
        return $this->hasMany(Issue::class, 'particular_id');
    }

    public function department() {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
