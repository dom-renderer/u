<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function store() {
        return $this->belongsTo(Store::class, 'type_id')->whereHas('designations', function ($query) {
            $query->where('type', 1);
        });
    }

    public function office() {
        return $this->belongsTo(CorporateOffice::class, 'type_id')->whereHas('designations', function ($query) {
            $query->where('type', 2);
        });
    }

    public function department() {
        return $this->belongsTo(Department::class, 'type_id')->whereHas('designations', function ($query) {
            $query->where('type', 3);
        });
    }
}
