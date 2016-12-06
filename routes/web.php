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

Route::get('/', 'ProductsController@index');

Route::post('/insertCart', 'ProductsController@insertCart');
//Route::post('/insertCart', function() {
//    return redirect('/login');
//});
Route::get('/cart', 'ProductsController@cart');
Route::post('/delete', 'ProductsController@destroy');
Route::post('/confirm', 'ProductsController@confirm');

Route::post('/showProducts', 'ProductsController@showProducts');
Route::post('/showCart', 'ProductsController@showCart');
Route::post('/showOrderHistory', 'ProductsController@showOrderHistory');
Route::post('/search', 'ProductsController@search');

Route::post('/autoPaging', 'ProductsController@autoPaging');
Route::post('/changeCart', 'ProductsController@changeCart');
Route::post('/deleteOrderHistory', 'ProductsController@deleteOrderHistory');


Auth::routes();
Route::get('/logout', function() {
    Auth::logout();
    return redirect('/');
});

//Route::get('/home', 'HomeController@index');
