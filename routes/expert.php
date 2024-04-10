<?php

use App\Http\Controllers\Api\V1\Expert\AuthExpertController;
use App\Http\Controllers\Api\V1\Expert\ExpertController;
use App\Http\Controllers\Api\V1\Expert\UserController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthExpertController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('send_code', 'sendCode');
    Route::post('verified_email', 'verifiedEmail');
    Route::post('reset_password', 'resetPassword');
    Route::get('logout', 'logout');

});
Route::middleware(['auth:sanctum', 'abilities:expert,access'])->controller(ExpertController::class)->group(function () {
    Route::get('get_profile', 'getProfile');
    Route::post('complete_information', 'completeInfo');
});
Route::middleware(['auth:sanctum', 'abilities:expert,access'])->controller(UserController::class)->group(function () {
    Route::get('get_appointments', 'getAppointments');
    Route::post('accept_appointment', 'acceptAppointment');
    Route::post('response_appointment', 'responseAppointment');

});
