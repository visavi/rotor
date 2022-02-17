<?php

use App\Http\Controllers\ApiController;
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

Route::controller(ApiController::class)
    ->middleware('check.token')
    ->group(function () {
        Route::get('/user', 'user');
        Route::get('/users/{login}', 'users');
        Route::get('/dialogues', 'dialogues');
        Route::get('/talk/{login}', 'talk');
        Route::get('/forums/{id}', 'forums');
        Route::get('/topics/{id}', 'topics');
    });
