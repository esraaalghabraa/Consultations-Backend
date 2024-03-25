<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpertDate extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded=[];

    public function expert(){
        return $this->belongsTo(Expert::class);
    }
    public function hour(){
        return $this->belongsTo(Hour::class);
    }
    public function day(){
        return $this->belongsTo(Day::class);
    }}
