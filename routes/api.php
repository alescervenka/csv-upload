<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/fileone', 'FileOne@store');
Route::get('/fileone/{recordid}', 'FileOne@show');

Route::post('/filetwo', 'FileTwo@store');
Route::get('/filetwo/{recordid}', 'FileTwo@show');

