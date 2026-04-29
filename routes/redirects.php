<?php

declare(strict_types=1);

use App\Http\Controllers\Load\DownController;
use Illuminate\Support\Facades\Route;

/* Временные редиректы на новые роуты */
Route::redirect('/forums/search', '/search', 301);
Route::redirect('/blogs/search', '/search', 301);
Route::redirect('/loads/search', '/search', 301);

Route::get('/downs/zip/{id}', [DownController::class, 'redirectZip']);
Route::get('/downs/zip/{id}/{fid}', [DownController::class, 'redirectZip']);

Route::redirect('/downs/comments/{id}', '/downs/{id}', 301);
Route::redirect('/downs/comment/{id}/{cid}', '/downs/{id}?cid={cid}', 301);
Route::redirect('/downs/end/{id}', '/downs/{id}', 301);
Route::get('/downs/{id}/comments', fn ($id) => redirect('/downs/' . $id, 301));
Route::redirect('/downs/rss/{id}', '/downs/{id}/rss', 301);
Route::redirect('/down/{id}', '/downs/{id}', 301);
Route::redirect('/down', '/downs', 301);
Route::redirect('/loads/top', '/downs?sort=rating', 301);

Route::redirect('/forum', '/forums', 301);
Route::redirect('/topics/votes/{id}', '/topics/{id}/vote', 301);
Route::redirect('/topics/print/{id}', '/topics/{id}/print', 301);
Route::redirect('/topics/{id}/{pid}', '/topics/{id}?pid={pid}', 301)->whereNumber('pid');
Route::redirect('/topics/end/{id}', '/topics/{id}', 301);
Route::redirect('/topics/rss/{id}', '/topics/{id}/rss', 301);
Route::redirect('/topic/{id}', '/topics/{id}', 301);
Route::redirect('/forums/top/topics', '/topics?sort=posts', 301);
Route::redirect('/forums/top/posts', '/posts?sort=rating', 301);

Route::redirect('/news/comments/{id}', '/news/{id}', 301);
Route::redirect('/news/comment/{id}/{cid}', '/news/{id}?cid={cid}', 301);
Route::redirect('/news/end/{id}', '/news/{id}', 301);
Route::get('/news/{id}/comments', fn ($id) => redirect('/news/' . $id, 301));

Route::redirect('/blog', '/blogs', 301);
Route::redirect('/blog/tags', '/blogs/tags', 301);
Route::redirect('/articles/comments/{id}', '/articles/{id}', 301);
Route::redirect('/articles/comment/{id}/{cid}', '/articles/{id}?cid={cid}', 301);
Route::redirect('/articles/rss/{id}', '/articles/{id}/rss', 301);
Route::redirect('/articles/print/{id}', '/articles/{id}/print', 301);
Route::redirect('/articles/end/{id}', '/articles/{id}', 301);
Route::get('/articles/{id}/comments', fn ($id) => redirect('/articles/' . $id, 301));
Route::redirect('/blogs/top', '/articles?sort=rating', 301);
Route::get('/blogs/active/articles', static function () {
    return redirect('/articles/active/articles?' . request()->server('QUERY_STRING'), 301);
});
Route::get('/blogs/active/comments', static function () {
    return redirect('/articles/active/comments?' . request()->server('QUERY_STRING'), 301);
});

Route::redirect('/photos/comments/{id}', '/photos/{id}', 301);
Route::redirect('/photos/comment/{id}/{cid}', '/photos/{id}?cid={cid}', 301);
Route::redirect('/photos/albums/{login}', '/photos/active/albums?user={login}', 301);
Route::redirect('/photos/comments/active/{login}', '/photos/active/comments?user={login}', 301);
Route::redirect('/photos/end/{id}', '/photos/{id}', 301);
Route::get('/photos/{id}/comments', fn ($id) => redirect('/photos/' . $id, 301));
Route::redirect('/photos/top', '/photos?sort=rating', 301);

Route::redirect('/offers/comments/{id}', '/offers/{id}', 301);
Route::redirect('/offers/comment/{id}/{cid}', '/offers/{id}?cid={cid}', 301);
Route::redirect('/offers/end/{id}', '/offers/{id}', 301);
Route::get('/offers/{id}/comments', fn ($id) => redirect('/offers/' . $id, 301));

Route::redirect('/votes/voters/{id}', '/votes/{id}/voters', 301);
