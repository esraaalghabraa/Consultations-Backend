<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['pivot', 'priority', 'category_id', 'deleted_at', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function experts()
    {
        return $this->belongsToMany(Expert::class, 'sub_category_experts');
    }
}
