<?php

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
Route::get('/terms-and-conditions', 'HomeController@terms_and_conditions')->name('terms-and-conditions');
Route::get('/cronjob', 'HomeController@cronFunction');
Route::get('/cronjob2', 'HomeController@cronFunction2');
Route::get('/cronjob3', 'HomeController@cronFunction3');
Route::get('/cronjob4', 'HomeController@cronFunction4');
//------------------ Admin Routes-------------------//
Route::group(['middleware'=>['auth','CheckAdmin']],function (){
    Route::get('/dashboard', 'AdminController@index')->name('dashboard');
});
