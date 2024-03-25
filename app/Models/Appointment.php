<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded=[];

    public function date()
    {
        $this->belongsTo(ExpertDate::class,'date_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function communicationType()
    {
        return $this->belongsTo(CommunicationType::class,'communication_type_id');
    }
}
