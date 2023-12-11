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
        Route::post('/mobile/register', 'App\Http\Controllers\loginController@RegisterMobile');
        Route::post('/login/mobile', 'App\Http\Controllers\loginController@loginwithmobile');
        Route::post('/login/verify/{id}', 'App\Http\Controllers\loginController@verifyCodeForLogin');
        Route::post('/login/resend/{id}', 'App\Http\Controllers\loginController@resendVerifyCode');
        Route::post('/login/password', 'App\Http\Controllers\loginController@loginWithPassword');
        Route::post('/password/verifycode/{id}', 'App\Http\Controllers\loginController@verifyCodeForResetPassword');
        Route::post('/password/reset/{id}', 'App\Http\Controllers\loginController@resetPassword');
    });
    Route::group(['prefix' => 'profiles'], function () {
        Route::post('/profile/{id}', 'App\Http\Controllers\profileController@profile');
        Route::post('/mobile/change', 'App\Http\Controllers\profileController@changeMobile');
        Route::post('/mobile/verify/{id}', 'App\Http\Controllers\profileController@verifyCodeForChangeMobile');
        Route::post('/mobile/reset/{id}', 'App\Http\Controllers\profileController@resetMobile');
    });
});
