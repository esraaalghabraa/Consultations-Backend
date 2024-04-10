<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Day;
use App\Models\Experience;
use App\Models\Expert;
use App\Models\ExpertExperience;
use App\Models\Hour;
use App\Models\SubCategory;
use App\Models\SubCategoryExpert;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Expert::create([
            "full_name" => "Esraa Alghabra",
            "email" => "esraaalghabraa@gmail.com",
            "phone" => "0934249783",
            "password" => Hash::make("123456789")
        ]);
        User::create([
            "full_name" => "Esraa Alghabra",
            "email" => "esraaalghabraa@gmail.com",
            "phone" => "0934249783",
            "password" => Hash::make("123456789")
        ]);
        $this->call([
            CommunicationTypeSeeder::class,
            DayAndHourSeeder::class
        ]);
        Category::factory(10)->create();
        SubCategory::factory(10)->create();
        Experience::factory(10)->create();
        ExpertExperience::factory(10)->create();
        Expert::factory(10)->create();
        SubCategoryExpert::factory(10)->create();
        SubCategoryExpert::where('expert_id', 1222)->get()->map(function ($element) {
            return $element->forceDelete();
        });
        $categories = Category::with('experts')->with(['subCategories' => function ($q) {
            return $q->with('experiences');
        }])->get();
        foreach ($categories as $category) {
            $sub_categories = $category->subCategories->toArray();
            foreach ($sub_categories as $sub_category) {
                $category->experiences_number = count($sub_category['experiences']);
            }
            $category->experts_number = count($category->experts->toArray());
            $category->sub_categories_number = count($sub_categories);
            $category->save();
        }
    }
}
