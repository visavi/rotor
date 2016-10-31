@extends('layout')

@section('title', 'Топ популярных тем - @parent')

@section('content')

    <h1>Топ популярных тем</h1>

    <?php foreach ($topics as $data): ?>
        <div class="b">

            <?php
            if ($data['topics_locked']) {
                $icon = 'fa-thumb-tack';
            } elseif ($data['topics_closed']) {
                $icon = 'fa-lock';
            } else {
                $icon = 'fa-folder-open';
            }
            ?>

            <i class="fa <?=$icon?> text-muted"></i>
            <b><a href="/topic/<?=$data['topics_id']?>"><?=$data['topics_title']?></a></b> (<?=$data['topics_posts']?>)
        </div>
        <div>
            Страницы:
            <?php forum_navigation('/topic/'.$data['topics_id'].'?', $config['forumpost'], $data['topics_posts']); ?>
            Автор: <?=$data['topics_author']?><br />
            Сообщение: <?=$data['topics_last_user']?> (<?=date_fixed($data['topics_last_time'])?>)
        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/forum/top?', $config['forumtem'], $start, $total); ?>
@stop
