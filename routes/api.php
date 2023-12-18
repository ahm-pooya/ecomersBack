<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'uploads'], function () {
        Route::post('/upload', 'App\Http\Controllers\uploadController@uploadImage');
        Route::get('/show/{file_id}', 'App\Http\Controllers\uploadController@showImage');
    });
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register/mobile', 'App\Http\Controllers\authController@RegisterMobile');
        Route::post('/register/password/{id}', 'App\Http\Controllers\authController@RegisterPassword');
        Route::post('/login/mobile', 'App\Http\Controllers\authController@loginwithmobile');
        Route::post('/login/verifycode/{id}', 'App\Http\Controllers\authController@verifyCodeForLogin');
        Route::post('/login/resendcode/{id}', 'App\Http\Controllers\authController@resendVerifyCodeForLogin');
        Route::post('/login/password', 'App\Http\Controllers\authController@loginWithPassword');
        Route::post('/mobile/resetpassword', 'App\Http\Controllers\authController@getMobileForResetPassword');
        Route::post('/verifycode/resetpassword/{id}', 'App\Http\Controllers\authController@verifyCodeForResetPassword');
        Route::post('/resetpassword/resendcode/{id}', 'App\Http\Controllers\authController@resendVerifyCodeForResetPassword');
        Route::post('/password/reset/{id}', 'App\Http\Controllers\authController@resetPassword');
        Route::post('/mobile/change', 'App\Http\Controllers\authController@changeMobile');
    });
    Route::group(['prefix' => 'profiles'], function () {
        Route::post('/profile/{id}', 'App\Http\Controllers\userProfileController@profile');
    });
});