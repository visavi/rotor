<?php

use App\Http\Controllers\Api\AccountApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\FileApiController;
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
Route::controller(ApiController::class)->group(function () {
    Route::post('/auth', 'auth')->middleware('throttle:api-auth');
    Route::get('/config', 'config');
    // Токен необязателен: гостю лента отдаётся без голосов
    Route::get('/feed', 'feed')->middleware('check.token.optional');
    // Поиск открыт и не зависит от пользователя, токен не нужен
    Route::get('/search', 'search');
});

// Регистрация и восстановление доступны гостю, там своя защита — капча и лимит запросов
Route::controller(AuthApiController::class)->group(function () {
    Route::get('/captcha', 'captcha');
    Route::post('/register', 'register')->middleware('throttle:api-auth');
    Route::post('/recovery', 'recovery')->middleware('throttle:api-auth');
});

// Чтение комментария открыто, как и страница записи
Route::get('/comments/{id}', [CommentApiController::class, 'show'])->whereNumber('id');

Route::middleware('check.token')->group(function () {
    Route::post('/comments', [CommentApiController::class, 'store']);
    Route::patch('/comments/{id}', [CommentApiController::class, 'update'])->whereNumber('id');
    Route::delete('/comments/{id}', [CommentApiController::class, 'destroy'])->whereNumber('id');

    Route::get('/files', [FileApiController::class, 'index']);
    Route::post('/files', [FileApiController::class, 'store']);
    Route::delete('/files/{id}', [FileApiController::class, 'destroy'])->whereNumber('id');
});

Route::controller(AccountApiController::class)
    ->middleware('check.token')
    ->prefix('user')
    ->group(function () {
        Route::patch('/', 'profile');
        Route::get('/settings', 'settings');
        Route::patch('/settings', 'updateSettings');
        Route::post('/password', 'password');
        Route::post('/email', 'email');
        Route::post('/verify', 'verify');
        Route::post('/status', 'status');
        Route::post('/color', 'color');
        Route::post('/apikey', 'apikey');
        Route::post('/photo', 'photo');
        Route::delete('/photo', 'deletePhoto');
    });

Route::controller(ApiController::class)
    ->middleware('check.token')
    ->group(function () {
        Route::get('/user', 'user');
        Route::get('/users/{login}', 'users');
        Route::get('/dialogues', 'dialogues');
        Route::get('/talk/{login}', 'talk');
        Route::get('/messages/new', 'newMessages');
        Route::post('/talk/{login}', 'createTalk');
        Route::post('/rating', 'rating');
    });
