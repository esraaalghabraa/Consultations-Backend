<?php

namespace App\Services;

use App\Exceptions\ExistObjectException;
use App\Models\Day;
use App\Models\ExpertDate;
use App\Models\Hour;
use App\Models\WorkTime;
use App\ResponseTrait;

class WorkTimeService
{
    use ResponseTrait;
    public function createWorkTimesToExpert($expert, $workTimes)
    {
        foreach ($workTimes as $item) {
            $day = Day::where('name', $item['day'])->first();
            $startTime = Hour::where('label', $item['start_time'])->first();
            $endTime = Hour::where('label', $item['end_time'])->first();
            if (!$day || !$startTime || !$endTime) {
                throw new ExistObjectException('day name or startTime or end_time not found');
            }
            WorkTime::create([
                'expert_id' => $expert->id,
                'day_id' => $day->id,
                'start_time_id' => $startTime->id,
                'end_time_id' => $endTime->id,
            ]);

            $hours_work = Hour::query()->whereBetween('time', [
                date('G:i a', strtotime($item['start_time'])),
                date('G:i a', strtotime($item['end_time']))
            ])->get();

            foreach ($hours_work as $hour) {
                ExpertDate::create([
                    'expert_id' => $expert->id,
                    'hour_id' => $hour->id,
                    'day_id' => $day->id,
                ]);
            }
        }
    }

}
