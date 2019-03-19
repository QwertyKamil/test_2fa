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

/*Auth::routes();*/

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

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
Route::post('authy/callback', 'Auth\AuthyController@callback');
