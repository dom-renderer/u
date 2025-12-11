<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DocumentUpload extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo( Document::class, 'document_id' );
    }

    public function store()
    {
        return $this->belongsTo( Store::class, 'location_id' );
    }
    
    public function storeCategory()
    {
        return $this->belongsTo( StoreCategory::class, 'location_category_id' );
    }

    public function users()
    {
        return $this->belongsToMany( User::class, 'document_users', 'document_upload_id', 'user_id' )->where('document_users.user_type', 0)->withTimestamps();
    }

    public function addusers()
    {
        return $this->belongsToMany( User::class, 'document_users', 'document_upload_id', 'user_id' )->where('document_users.user_type', 1)->withTimestamps();
    }

    public function getAttachmentPathAttribute() {

        if ( !empty(trim($this->file_name)) && file_exists( public_path( "storage/documents/{$this->file_name}" ) ) ) {
            return asset( "storage/documents/{$this->file_name}" );
        }
        return '';
    }

}
