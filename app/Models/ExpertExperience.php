<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpertExperience extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $hidden=['expert_id','experience_id','deleted_at','created_at','updated_at'];


}
