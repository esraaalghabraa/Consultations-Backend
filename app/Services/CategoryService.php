<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getCategoriesWithSubcategoriesAndExperts()
    {
        return Category::whereHas('subCategories')
            ->with('subCategories')
            ->whereHas('experts')
            ->get();
    }
}
