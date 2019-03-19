<?php

namespace App;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Cartalyst\Sentinel\Users\EloquentUser;

class User extends EloquentUser implements CanResetPassword
{
    use Notifiable ;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'phone_number', 'country_code','remember_token'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'authy_status', 'authy_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param $authy_id string
     */
    public function updateAuthyId($authy_id)
    {
        if($this->authy_id != $authy_id) {
            $this->authy_id = $authy_id;
            $this->save();
        }
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $notification = new ResetPasswordNotification($token);
        $this->notify($notification);
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }
}
