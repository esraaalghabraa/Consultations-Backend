<?php

use App\Http\Controllers\Api\V1\Guest\HomeController;
use Illuminate\Support\Facades\Route;

Route::controller(HomeController::class)->group(function (){
    Route::get('get_home','getHomeData');
    Route::get('get_experts','getExperts');
    Route::get('get_main_category_details','getMainCategoryDetails');
    Route::get('get_expert_details','getExpertDetails');
    Route::get('search','search');
});
require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/expert.php';
