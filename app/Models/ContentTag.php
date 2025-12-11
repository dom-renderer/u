<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ContentTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function tag() {
        return $this->belongsTo(Tag::class, 'tag_id');
    }

    public function content() {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
