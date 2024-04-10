<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Expert extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['password', 'remember_token', 'pivot', 'otp', 'is_complete_data', 'email_verified_at', 'category_id', 'deleted_at', 'created_at', 'updated_at'];

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class, 'sub_category_experts');
    }

    public function expertDates()
    {
        return $this->hasMany(ExpertDate::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function experiences()
    {
        return $this->belongsToMany(Experience::class, 'expert_experiences');
    }

    public function communicationTypes()
    {
        return $this->belongsToMany(CommunicationType::class, 'expert_communications');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'appointments');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
