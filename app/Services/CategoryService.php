<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Retrieve all categories that have subcategories and experts.
     *
     * @return Collection
     */
    public function getCategoriesWithSubcategoriesAndExperts(): Collection
    {
        // Fetch categories that have associated subcategories and experts
        return Category::whereHas('subCategories') // Ensure the category has subcategories
        ->with('subCategories')               // Load the subcategories relationship
        ->whereHas('experts')                 // Ensure the category has associated experts
        ->get();                              // Retrieve the results
    }
}
