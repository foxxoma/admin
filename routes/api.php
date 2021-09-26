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

use App\Http\Controllers\AuthController;

Route::post('/user/create', [AuthController::class, 'create']);
Route::post('/user/authenticate', [AuthController::class, 'authenticate']);


Route::middleware(['auth:api', 'admin'])->group(function() {
	Route::post('/admin/start', 'App\Http\Controllers\AdminController@start');
	Route::post('/admin/getFilesProp', 'App\Http\Controllers\AdminController@getFilesProp');
	Route::post('/admin/getViewTables', 'App\Http\Controllers\AdminController@getViewTables');
	Route::post('/admin/setViewTables', 'App\Http\Controllers\AdminController@setViewTables');
	Route::post('/admin/getTables', 'App\Http\Controllers\AdminController@getTables');
	Route::post('/admin/getTable', 'App\Http\Controllers\AdminController@getTable');
	Route::post('/admin/editRow', 'App\Http\Controllers\AdminController@editRow');
	Route::post('/admin/deleteRow', 'App\Http\Controllers\AdminController@deleteRow');
});
