<?php

namespace Database\Seeders;

use App\Models\CommunicationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommunicationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CommunicationType::create([
            "name" => "message",
            "cost" => 5
        ]);
        CommunicationType::create([
            "name" => "voice",
            "cost" => 10
        ]);
        CommunicationType::create([
            "name" => "video",
            "cost" => 15
        ]);
    }
}
