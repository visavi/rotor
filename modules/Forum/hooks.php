<?php

use App\Classes\Hook;
use App\Classes\Restatement;
use App\Http\Controllers\AjaxController;
use App\Models\Search;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Forum\Models\Post;
use Modules\Forum\Models\Topic;
use Modules\Forum\Observers\PostObserver;
use Modules\Forum\Observers\TopicObserver;

Topic::observe(TopicObserver::class);
Post::observe(PostObserver::class);

// Жалобы на посты
AjaxController::$extraComplaintTypes[Post::$morphName] = function (int $id, mixed $page): array {
    $model = Post::query()->find($id);
    $path = $model ? route('topics.topic', ['id' => $model->topic_id, 'pid' => $model->id], false) : null;

    return ['model' => $model, 'path' => $path];
};

// Загрузка файлов к постам
AjaxController::$extraFileTypes[] = Post::$morphName;

// Пересчет форума
Restatement::register('forums', function () {
    DB::update('update topics set count_posts = (select count(*) from posts where topics.id = posts.topic_id)');
    DB::update('update forums set count_topics = (select count(*) from topics where forums.id = topics.forum_id)');
    DB::update('update forums set count_posts = (select coalesce(sum(count_posts), 0) from topics where forums.id = topics.forum_id)');
});

Restatement::register('votes', function () {
    DB::update('update votes set count = (select coalesce(sum(result), 0) from voteanswer where votes.id = voteanswer.vote_id)');
});

// Удаление данных при удалении пользователя
User::$extraDeleteCallbacks[] = function (User $user): void {
    \Modules\Forum\Models\Bookmark::query()->where('user_id', $user->id)->delete();
};

// Регистрация eager loading для поиска
Search::$morphWith[Post::class]  = ['topic'];
Search::$morphWith[Topic::class] = ['forum', 'lastPost'];

// Регистрация поиска по постам (дополнительный morph помимо topics)
Search::$types[Post::$morphName]   = __('index.posts');
Search::$viewMap[Post::$morphName] = 'forum::search/_posts';

// Ссылки на форум в анкете пользователя
Hook::add('userProfileLinks', function (string $content, $user) {
    if (! Route::has('forums.active-topics')) {
        return $content;
    }

    $link = ' / <b><a href="' . route('forums.active-topics', ['user' => $user->login]) . '">' . __('index.forums') . '</a></b>'
        . ' (<a href="' . route('forums.active-posts', ['user' => $user->login]) . '">' . __('main.messages') . '</a>)';

    return $content . $link;
});

// Ссылка в боковом меню
Hook::add('sidebarMenuStart', function (string $content) {
    if (! Route::has('forums.index')) {
        return $content;
    }

    $url    = route('forums.index');
    $active = request()->is('forums*', 'topics*') ? ' active' : '';
    $label  = __('index.forums');
    $stats  = statsForum();

    return '<li>
        <a class="menu-item' . $active . '" href="' . $url . '">
            <i class="menu-icon far fa-comment-alt"></i>
            <span class="menu-label">' . $label . '</span>
            <span class="badge menu-badge">' . $stats . '</span>
        </a>
    </li>' . PHP_EOL . $content;
}, 5);

// Блок форума в панели администратора
Hook::add('adminBlockEditor', function (string $content) {
    if (! Route::has('admin.forums.index')) {
        return $content;
    }

    $url   = route('admin.forums.index');
    $label = __('index.forums');
    $stats = statsForum();

    return $content
        . '<i class="far fa-circle text-muted"></i> <a href="' . $url . '">' . $label . '</a> <span class="badge bg-adaptive">' . $stats . '</span><br>' . PHP_EOL;
});

// Ссылка на настройки в навигации настроек
Hook::add('adminSettingsNav', function (string $content) {
    if (! Route::has('forum.settings')) {
        return $content;
    }

    $url   = route('forum.settings');
    $label = __('forum::forums.settings');

    return $content . '<a class="nav-link" href="' . $url . '">' . $label . '</a>' . PHP_EOL;
});
