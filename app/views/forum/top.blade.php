@extends('layout')

@section('title')
    Топ популярных тем - @parent
@stop

@section('content')

    <h1>Топ популярных тем</h1>

    <a href="/forum">Форум</a>

    <?php foreach ($topics as $data): ?>
        <div class="b">

            <?php
            if ($data['locked']) {
                $icon = 'fa-thumb-tack';
            } elseif ($data['closed']) {
                $icon = 'fa-lock';
            } else {
                $icon = 'fa-folder-open';
            }
            ?>

            <i class="fa <?=$icon?> text-muted"></i>
            <b><a href="/topic/<?=$data['id']?>"><?=$data['title']?></a></b> (<?=$data['posts']?>)
        </div>
        <div>
            <?= App::forumPagination($data)?>
            Автор: <?=$data['author']?><br />
            Сообщение: <?=$data['last_user']?> (<?=date_fixed($data['last_time'])?>)
        </div>
    <?php endforeach; ?>

    <?php App::pagination($page) ?>
@stop
