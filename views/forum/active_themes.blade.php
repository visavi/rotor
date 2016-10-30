@extends('layout')

@section('title', 'Список тем '.e($user).' - @parent')

@section('content')

    <h1>Список тем {{ $user }}</h1>

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
            Форум: <a href="/forum/<?=$data['topics_forums_id']?>"><?=$data['forums_title']?></a><br />
            Автор: <?=nickname($data['topics_author'])?> / Посл.: <?=nickname($data['topics_last_user'])?> (<?=date_fixed($data['topics_last_time'])?>)
        </div>

    <?php endforeach; ?>

    <?php page_strnavigation('/forum/active/themes?user='.$user.'&amp;', $config['forumtem'], $start, $total); ?>
@stop
