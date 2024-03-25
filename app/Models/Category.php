<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded=[];

    protected function image(): Attribute{
        return Attribute::make(
            get:fn ($value) => ($value != null) ? asset($value) : null
        );
    }

    public function subCategories(){
        return $this->hasMany(SubCategory::class);
    }

    public function experts(){
        return $this->hasMany(Expert::class);
    }
}
