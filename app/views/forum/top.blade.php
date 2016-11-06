@extends('layout')

@section('title', 'Топ популярных тем - @parent')

@section('content')

    <h1>Топ популярных тем</h1>

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
            Страницы:
            <?php forum_navigation('/topic/'.$data['id'].'?', $config['forumpost'], $data['posts']); ?>
            Автор: <?=$data['author']?><br />
            Сообщение: <?=$data['last_user']?> (<?=date_fixed($data['last_time'])?>)
        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/forum/top?', $config['forumtem'], $start, $total); ?>
@stop
