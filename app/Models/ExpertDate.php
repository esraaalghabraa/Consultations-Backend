<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpertDate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'expert_dates';
    protected $hidden = ['expert_id', 'day_id', 'hour_id', 'deleted_at', 'created_at', 'updated_at'];

    public function appointment()
    {
        $this->belongsTo(Appointment::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function hour()
    {
        return $this->belongsTo(Hour::class);
    }

    public function day()
    {
        return $this->belongsTo(Day::class);
    }

}
