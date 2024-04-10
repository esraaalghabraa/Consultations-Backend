<?php

use App\Http\Controllers\Api\V1\Guest\HomeController;
use Illuminate\Support\Facades\Route;

Route::controller(HomeController::class)->group(function (){
    Route::get('get_home','getHomeDate');
    Route::get('get_recommended_experts','getRecommendedExperts');
    Route::get('get_top_experts','getTopExperts');
    Route::get('get_sub_categories_with_experts','getSubCategoriesWithExperts');
    Route::get('get_sub_category_experts','getSubCategoryExperts');
    Route::get('get_expert_details','getExpertDetails');
    Route::get('search','search');
});
