<?php

use App\Http\Controllers\Api\V1\Guest\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('send_code', 'sendCode');
    Route::post('verified_email', 'verifiedEmail');
    Route::post('reset_password', 'resetPassword');
    Route::get('logout', 'logout');
});
