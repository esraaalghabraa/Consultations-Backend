<?php

namespace Database\Seeders;

use App\Models\Day;
use App\Models\Hour;
use Illuminate\Database\Seeder;

class DayAndHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Day::create([
            'name'=>'Saturday',
        ]);
        Day::create([
            'name'=>'Sunday',
        ]);
        Day::create([
            'name'=>'Monday',
        ]);
        Day::create([
            'name'=>'Tuesday',
        ]);
        Day::create([
            'name'=>'Wednesday',
        ]);
        Day::create([
            'name'=>'Thursday',
        ]);
        Day::create([
            'name'=>'Friday',
        ]);

        for ($j=0; $j<24; $j++){
            Hour::create([
                'time' => $j.':00',
                'label' => $j==0 ? '12:00 AM' :($j>=12 ? ($j%12==0 ? '12:00 PM': ($j%12<10? '0'.$j%12 .':00 PM':$j%12 .':00 PM')): $j.':00 AM'),
            ]);
        }
    }
}
