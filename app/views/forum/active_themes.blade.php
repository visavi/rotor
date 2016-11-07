@extends('layout')

@section('title', 'Список тем '.e($user).' - @parent')

@section('content')

    <h1>Список тем {{ $user }}</h1>

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
            Форум: <a href="/forum/<?=$data['forums_id']?>"><?=$data['forum_title']?></a><br />
            Автор: <?=nickname($data['author'])?> / Посл.: <?=nickname($data['last_user'])?> (<?=date_fixed($data['last_time'])?>)
        </div>

    <?php endforeach; ?>

    <?php page_strnavigation('/forum/active/themes?user='.$user.'&amp;', $config['forumtem'], $start, $total); ?>
@stop
