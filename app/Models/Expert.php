<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Expert extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class, 'sub_category_experts');
    }

    public function expertDates()
    {
        return $this->hasMany(ExpertDate::class);
    }

    public function workTimes()
    {
        return $this->hasMany(WorkTime::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }



    public function communicationTypes(): BelongsToMany
    {
        return $this->belongsToMany(CommunicationType::class, 'communication_type_expert');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
