<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



	Route::post("create", "UserCreationController@createNewUser");

	Route::post("login", "UserSignInController@loginUser");

	Route::get("all", "PageGenerationController@getAllInfo");

	Route::get("details", "ConfidentialInfoController@accessInfo");
	Route::post("details", "ConfidentialInfoController@createInfo");
	Route::post("removal", "ConfidentialInfoController@removeInfo");
