<?php

use App\Http\Controllers\Api\V1\Guest\AuthController;
use App\Http\Controllers\Api\V1\User\ExpertController;
use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Support\Facades\Route;


//Route::middleware(['auth:sanctum', 'abilities:user,access'])->group(function (){
    Route::controller(ExpertController::class)->group(function (){
        Route::post('change_favorite','changeFavorite');
        Route::post('add_review','addReview');
        Route::post('book_appointment','addBooking');
    });
    Route::controller(UserController::class)->group(function (){
        Route::get('get_profile','getProfile');
        Route::post('edit_profile','editProfile');
        Route::get('get_appointments','getAppointments');
        Route::post('get_favorite','getFavorite');
    });
//});
