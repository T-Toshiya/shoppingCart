<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

//Route::get('/', function () {
//    return view('users.index');
//});
Route::get('/', 'ProductsController@index');
Route::post('/insertCart', 'ProductsController@insertCart');

Route::get('/cart', 'ProductsController@cart');
Route::post('/delete', 'ProductsController@destroy');
Route::post('/confirm', 'ProductsController@confirm');
Route::get('/orderHistory', 'ProductsController@showOrderHistory');


Auth::routes();

Route::get('/home', 'HomeController@index');
