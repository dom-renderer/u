<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DepartmentUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function dept() {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
