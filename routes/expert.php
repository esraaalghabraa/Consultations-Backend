<?php

use App\Http\Controllers\Api\V1\Expert\ExpertController;
use Illuminate\Support\Facades\Route;


Route::prefix('expert')
    ->middleware(['auth:sanctum', 'abilities:user,access','role_expert'])
    ->controller(ExpertController::class)->group(function () {
    Route::get('get_categories', 'getCategories');
    Route::get('get_profile', 'getProfile');
    Route::post('complete_information', 'completeInfo');
});
//Route::middleware(['auth:sanctum', 'abilities:expert,access'])->controller(UserController::class)->group(function () {
//    Route::get('get_appointments', 'getAppointments');
//    Route::post('accept_appointment', 'acceptAppointment');
//    Route::post('response_appointment', 'responseAppointment');
//
//});
