<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkTime extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['expert_id', 'day_id', 'start_time_id', 'end_time_id', 'deleted_at', 'created_at', 'updated_at'];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function startTime()
    {
        return $this->belongsTo(Hour::class);
    }
    public function endTime()
    {
        return $this->belongsTo(Hour::class);
    }

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
}
