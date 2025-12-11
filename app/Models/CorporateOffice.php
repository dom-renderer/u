<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CorporateOffice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function designations() {
        return $this->hasMany(CorporateOffice::class, 'type_id')->where('type', 2);
    }
}
