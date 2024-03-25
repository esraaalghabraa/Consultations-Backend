<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubCategoryExpert>
 */
class SubCategoryExpertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = Category::with('experts')->with('subCategories')->find(rand(1,10));
        $sub_categories = $category->subCategories->map(function ($sub_category){
            return $sub_category->id;
        })->toArray();
        $experts = $category->experts->map(function ($expert){
            return $expert->id;
        })->toArray();
        if (!empty($sub_categories)&&!empty($experts)){
            return [
                'expert_id'=>fake()->randomElement($sub_categories),
                'sub_category_id'=>fake()->randomElement($experts),
            ];
        }else
            return [
                'expert_id'=>1222,
                'sub_category_id'=>1222,
            ];
    }
}
