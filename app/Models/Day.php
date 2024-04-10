<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function dates()
    {
        $this->hasMany(ExpertDate::class);
    }
}
