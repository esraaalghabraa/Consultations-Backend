<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expert extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded=[];
    protected $hidden=['pivot'];

    public function subCategories(){
        return $this->belongsToMany(SubCategory::class,'sub_category_experts');
    }
    public function dates(){
        $this->hasMany(ExpertDate::class);
    }

    public function experiences(){
        return $this->belongsToMany(Experience::class,'expert_experiences');
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
