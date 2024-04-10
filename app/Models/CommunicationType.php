<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['pivot'];

    public function experts()
    {
        return $this->belongsToMany(Expert::class, 'expert_experiences');
    }
}
