<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OperationManager extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function opsmgr() {
        return $this->belongsTo(User::class, 'ops_id');
    }

    public function dom() {
        return $this->belongsTo(User::class, 'dom_id');
    }
}
