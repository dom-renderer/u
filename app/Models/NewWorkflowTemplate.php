<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewWorkflowTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'sections' => 'array'
    ];

    public function children() {
        return $this->hasMany(NewWorkflowTemplateItem::class, 'new_workflow_template_id');
    }
}
