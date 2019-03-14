<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('two-factor', 'Auth\LoginController@getTwoFactor');
Route::post('two-factor', 'Auth\LoginController@postTwoFactor');

Route::get('authy/status', 'Auth\AuthyController@status');
Route::get('getUserData', function () {
    if ($user = Sentinel::check()) {
        return response()->json($user);
    } else {
        return response()->json(['error' => true, 'message' => "You must be logged in."], 403);
    }
});
Route::post('authy/callback', 'Auth\AuthyController@callback')->middleware('validate_authy');
