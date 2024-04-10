<?php

use App\Models\Expert;
use Illuminate\Support\Facades\Route;

//Route::get('/',[\App\Http\Controllers\Api\HomeController::class,'search']);
Route::get('/', function () {
    return view('email_verification');
});
