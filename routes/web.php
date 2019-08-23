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

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])
    ->group( function (){

        Route::get('users','UsersController@index');
        Route::get('message/{id}','ChatsController@show');
        Route::post('message','ChatsController@store');

});
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
