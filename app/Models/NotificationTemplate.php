<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public static function scopeEmail($query) {
        return $query->where('type', 0);
    }

    public static function scopePushNotification($query) {
        return $query->where('type', 1);
    }

    public static function scopeActive($query) {
        return $query->where('status', 1);
    }

    public function scopeNoncompletion($query) {
        return $query->where('completion_type', 0);
    }

    public function scopeCompletion($query) {
        return $query->where('completion_type', 1);
    }


    public static function typeOf($type) {
        if ($type == 0) {
            return 'email';
        } else if ($type == 1) {
            return 'push notification';
        } else {
            return '';
        }
    }
}
