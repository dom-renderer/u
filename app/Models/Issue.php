<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function particular()
    {
        return $this->belongsTo(Particular::class, 'particular_id');
    }

    public function users()
    {
        return $this->hasMany(UserIssue::class, 'issue_id');
    }
}
