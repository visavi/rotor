<?php

use Illuminate\Support\Facades\Route;
use Modules\Forum\Controllers\ActiveController;
use Modules\Forum\Controllers\BookmarkController;
use Modules\Forum\Controllers\ForumController;
use Modules\Forum\Controllers\NewController;
use Modules\Forum\Controllers\TopicController;
use Modules\Forum\Controllers\Admin\ForumController as AdminForumController;
use Modules\Forum\Controllers\Admin\ForumSettingController;

/* ---- Публичные роуты ---- */
Route::prefix('forums')
    ->name('forums.')
    ->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('index');
        Route::get('/{id}', [ForumController::class, 'forum'])->name('forum');

        Route::get('/rss', [ForumController::class, 'rss'])->name('rss');
        Route::match(['get', 'post'], '/create', [ForumController::class, 'create'])->name('create');

        Route::get('/active/posts', [ActiveController::class, 'posts'])->name('active-posts');
        Route::get('/active/topics', [ActiveController::class, 'topics'])->name('active-topics');
        Route::delete('/active/{id}/delete', [ActiveController::class, 'destroy'])->name('active-delete');

        Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks');
        Route::post('/bookmarks/delete', [BookmarkController::class, 'delete'])->name('bookmarks.delete');
        Route::post('/bookmarks/perform', [BookmarkController::class, 'perform'])->name('bookmarks.perform');
    });

Route::prefix('topics')
    ->name('topics.')
    ->group(function () {
        Route::get('/', [NewController::class, 'topics'])->name('index');

        Route::get('/{id}', [TopicController::class, 'index'])->name('topic');
        Route::post('/{id}/vote', [TopicController::class, 'vote'])->name('vote');
        Route::post('/{id}/open', [TopicController::class, 'open'])->name('open');
        Route::post('/{id}/close', [TopicController::class, 'close'])->name('close');
        Route::post('/{id}/create', [TopicController::class, 'create'])->name('create');
        Route::post('/{id}/delete', [TopicController::class, 'delete'])->name('delete');
        Route::get('/{id}/print', [TopicController::class, 'print'])->name('print');
        Route::match(['get', 'post'], '/{id}/edit', [TopicController::class, 'edit'])->name('edit');

        Route::get('/{id}/rss', [ForumController::class, 'rssPosts'])->name('rss');
    });

Route::prefix('posts')
    ->name('posts.')
    ->group(function () {
        Route::get('/', [NewController::class, 'posts'])->name('index');
        Route::match(['get', 'post'], '/{id}/edit', [TopicController::class, 'editPost'])->name('edit');
    });

/* ---- Админ роуты ---- */
Route::middleware(['web', 'check.admin'])
    ->controller(AdminForumController::class)
    ->prefix('admin/forums')
    ->name('admin.forums.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'forum')->name('forum');
        Route::post('/create', 'create')->name('create');
        Route::match(['get', 'post'], '/{id}/edit', 'edit')->name('edit');
        Route::delete('/{id}/delete', 'delete')->name('delete');
        Route::post('/restatement', 'restatement')->name('restatement');
    });

Route::middleware(['web', 'check.admin'])
    ->controller(AdminForumController::class)
    ->prefix('admin/topics')
    ->name('admin.topics.')
    ->group(function () {
        Route::get('/{id}', 'topic')->name('topic');
        Route::match(['get', 'post'], '/{id}/edit', 'editTopic')->name('edit');
        Route::match(['get', 'post'], '/{id}/move', 'moveTopic')->name('move');
        Route::post('/{id}/action', 'actionTopic')->name('action');
        Route::delete('/{id}/delete', 'deleteTopic')->name('delete');
    });

Route::middleware(['web', 'check.admin'])
    ->controller(AdminForumController::class)
    ->prefix('admin/posts')
    ->name('admin.posts.')
    ->group(function () {
        Route::match(['get', 'post'], '/{id}/edit', 'editPost')->name('edit');
        Route::post('/delete', 'deletePosts')->name('delete');
    });

/* ---- Настройки форума ---- */
Route::middleware(['web', 'check.admin'])
    ->controller(ForumSettingController::class)
    ->prefix('admin/forum-settings')
    ->name('forum.')
    ->group(function () {
        Route::get('/', 'index')->name('settings');
        Route::post('/', 'update')->name('settings.update');
    });

/* ---- API роуты ---- */
Route::middleware(['api', 'check.token'])
    ->prefix('api')
    ->group(function () {
        Route::get('/forums', [\Modules\Forum\Controllers\Api\ForumApiController::class, 'categoryForums']);
        Route::get('/forums/{id}', [\Modules\Forum\Controllers\Api\ForumApiController::class, 'forums']);
        Route::post('/forums/{id}', [\Modules\Forum\Controllers\Api\ForumApiController::class, 'createTopic']);
        Route::get('/topics/{id}', [\Modules\Forum\Controllers\Api\ForumApiController::class, 'topics']);
        Route::post('/topics/{id}', [\Modules\Forum\Controllers\Api\ForumApiController::class, 'createPost']);
    });
