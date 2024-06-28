<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['time','deleted_at', 'created_at', 'updated_at'];

    public function dates()
    {
        $this->hasMany(ExpertDate::class);
    }
}
