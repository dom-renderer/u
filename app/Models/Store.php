<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function designations() {
        return $this->hasMany(Designation::class, 'type_id')->where('type', 1);
    }

    public function thecity() {
        return $this->belongsTo(City::class, 'city', 'city_id');
    }

    public function dom() {
        return $this->belongsTo(User::class, 'dom_id');
    }

    public function storetype() {
        return $this->belongsTo(StoreType::class, 'store_type');
    }

    public function modeltype() {
        return $this->belongsTo(ModelType::class, 'model_type');
    }

    public function storecategory() {
        return $this->belongsTo(StoreCategory::class, 'store_category');
    }
}