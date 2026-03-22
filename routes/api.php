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

Route::controller(ApiController::class)->group(function () {
    Route::post('/auth', 'auth');
    Route::get('/config', 'config');
});

Route::controller(ApiController::class)
    ->middleware('check.token')
    ->group(function () {
        Route::get('/user', 'user');
        Route::get('/users/{login}', 'users');
        Route::get('/dialogues', 'dialogues');
        Route::get('/talk/{login}', 'talk');
        Route::get('/messages/new', 'newMessages');
        Route::post('/messages/send', 'send'); // deprecated, используйте POST /talk/{login}
        Route::post('/talk/{login}', 'createTalk');
        Route::get('/forums', 'categoryForums');
        Route::get('/forums/{id}', 'forums');
        Route::post('/forums/{id}', 'createTopic');
        Route::get('/topics/{id}', 'topics');
        Route::post('/topics/{id}', 'createPost');
    });
