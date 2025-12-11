<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ContentAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function analytics()
    {
        return $this->hasOne(ContentAnalytic::class, 'content_attachment_id');
    }
}
