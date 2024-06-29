<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['pivot','deleted_at', 'created_at', 'updated_at'];

    public function experts(): BelongsToMany
    {
        return $this->belongsToMany(Expert::class, 'communication_type_expert');
    }
}
