<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function designations() {
        return $this->hasMany(Designation::class, 'type_id')->where('type', 3);
    }

    public function users() {
        return $this->hasMany(DepartmentUser::class, 'department_id');
    }

    public function escalations()
    {
        return $this->hasMany(TicketitEscalation::class, 'department_id');
    }

    public function particulars()
    {
        return $this->hasMany(Particular::class, 'department_id');        
    }
}