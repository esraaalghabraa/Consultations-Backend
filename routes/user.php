<?php

use App\Http\Controllers\Api\V1\Expert\AuthExpertController;
use App\Http\Controllers\Api\V1\User\AuthUserController;
use App\Http\Controllers\Api\V1\User\ExpertController;
use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(AuthUserController::class)->group(function (){
    Route::post('register','register');
    Route::post('login','login');
    Route::post('send_code','sendCode');
    Route::post('verified_email','verifiedEmail');
    Route::post('reset_password','resetPassword')->middleware(['auth:sanctum', 'abilities:expert,access']);
    Route::get('logout','logout')->middleware(['auth:sanctum', 'abilities:expert,access']);
});
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
