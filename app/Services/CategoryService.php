<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Retrieve all categories with their subcategories.
     * Optionally include only those that have experts.
     *
     * @param bool $withExperts
     * @return Collection
     */
    public function getCategoriesWithSubcategories(bool $withExperts = false): Collection
    {
        $query = Category::whereHas('subCategories') // Ensure the category has subcategories
        ->with('subCategories');               // Load the subcategories relationship

        if ($withExperts) {
            $query->whereHas('experts');           // Optionally ensure the category has associated experts
        }

        return $query->get();                       // Retrieve the results
    }
}
