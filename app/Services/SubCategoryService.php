<?php

namespace App\Services;

use App\Models\SubCategoryExpert;


class SubCategoryService
{
    public function addSubCategoriesToExpert($expert, $subCategoryIds)
    {
        foreach ($subCategoryIds as $item) {
            SubCategoryExpert::create([
                'expert_id' => $expert->id,
                'sub_category_id' => $item,
            ]);
        }
    }
}
