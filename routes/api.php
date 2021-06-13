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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::pattern('id', '\d+');
Route::pattern('login', '[\w\-]+');

Route::group(['middleware' => 'check.token'], function () {
    Route::get('/user', [\App\Http\Controllers\ApiController::class, 'user']);
    Route::get('/users/{login}', [\App\Http\Controllers\ApiController::class, 'users']);
    Route::get('/dialogues', [\App\Http\Controllers\ApiController::class, 'dialogues']);
    Route::get('/talk/{login}', [\App\Http\Controllers\ApiController::class, 'talk']);
    Route::get('/forums/{id}', [\App\Http\Controllers\ApiController::class, 'forums']);
    Route::get('/topics/{id}', [\App\Http\Controllers\ApiController::class, 'topics']);
});
