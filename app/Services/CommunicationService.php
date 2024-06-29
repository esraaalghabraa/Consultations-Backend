<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CommunicationService
{
    public function addCommunicationTypesToExpert($expert, $communicationTypeIds)
    {
        foreach ($communicationTypeIds as $item) {
            DB::table('communication_type_expert')->insert([
                'expert_id' => $expert->id,
                'communication_type_id' => $item,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
