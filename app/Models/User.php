<?php

namespace App\Models;

use App\Mail\ResetPasswordMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
       $this->attributes['password'] = bcrypt($value);
    }

    public function findForPassport($username) {
        return $this->where('id', $username)->first();
    }

    public function sendPasswordResetNotification($token) {   
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            Mail::to($this->email)->send(new ResetPasswordMail($this, $token));
        }
    }


    public function tokens() {
        return $this->hasMany(DeviceToken::class, 'user_id');
    }

    public function destore() {
        return $this->hasMany(Designation::class, 'user_id')->where('type', 1);
    }

    public function deoffice() {
        return $this->hasMany(Designation::class, 'user_id')->where('type', 2);
    }

    public function dedepartment() {
        return $this->hasMany(Designation::class, 'user_id')->where('type', 3);
    }

    public function depuser() {
        return $this->hasOne(DepartmentUser::class, 'user_id');
    }

    public static function isAdmin() {
        return in_array('Admin', auth()->user()->getRoleNames()->toArray());
    }

    public function tRoles()
    {
        return $this->hasMany(TicketRoleUser::class, 'user_id');
    }
}
