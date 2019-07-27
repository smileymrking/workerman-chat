<?php

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
|
*/
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->name('auth.')
    ->namespace('Auth')
    ->group(function (){
        Route::post('/register', 'RegisterController@register')->name('register');
        Route::post('/login', 'LoginController@login')->name('login');
        Route::post('/refresh', 'LoginController@refresh')->name('refresh');
        Route::middleware('auth')->get('/home', function () {
            return response()->json(['status'=>'success','code'=>200,'message'=>"It's OK"]);
        })->name('home');
    });
