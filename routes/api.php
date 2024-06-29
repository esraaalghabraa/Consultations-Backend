<?php

use App\Http\Controllers\Api\V1\Guest\HomeController;
use Illuminate\Support\Facades\Route;

Route::controller(HomeController::class)->group(function (){
    Route::get('get_home','getHomeData');
    Route::get('get_experts','getExperts');
});
require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/expert.php';
