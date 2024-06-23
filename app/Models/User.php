<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, Notifiable,SoftDeletes,HasRolesAndPermissions;

    protected $guarded = [];

    protected $dates = ['otp_last_sent_at'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function experts()
    {
        return $this->belongsToMany(Expert::class, 'appointments');
    }

    public function favoriteExperts()
    {
        return $this->belongsToMany(Expert::class, 'favorites');
    }
}
