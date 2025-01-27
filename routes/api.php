<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'],function(){
    Route::middleware(['AuthGuard'])->group(function(){
        Route::get('admins','App\Http\Controllers\Users@getAdmin');
        Route::post('users','App\Http\Controllers\Users@createUser');
        Route::get('users','App\Http\Controllers\Users@getUser');
        Route::put('users/{id}','App\Http\Controllers\Users@userUpdate');
        Route::delete('users/{id}','App\Http\Controllers\Users@userDelete');
    });

    Route::group(['prefix' => 'games'], function(){
        Route::post('/{slug}/upload','App\Http\Controllers\Games@uploadGame');
        Route::middleware(['AuthGuard'])->group(function(){
            Route::post('/{slug}/scores','App\Http\Controllers\Scores@addScore');
            Route::get('/{slug}/scores','App\Http\Controllers\Scores@getScore');
            Route::post('/{slug}/{version}','App\Http\Controllers\Games@serveGame');
            Route::post('','App\Http\Controllers\Games@createGame');
            Route::put('/{slug}','App\Http\Controllers\Games@updateGame');
            Route::delete('/{slug}','App\Http\Controllers\Games@deleteGame');
        });
    });
    Route::group(['prefix' => 'auth'],function(){
        Route::post('signup','App\Http\Controllers\Authentication@SignUp');
        Route::post('signin','App\Http\Controllers\Authentication@SignIn');
        Route::post('signout','App\Http\Controllers\Authentication@SignOut')->middleware(['AuthGuard']);
    }); 
});
