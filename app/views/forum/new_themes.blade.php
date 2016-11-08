@extends('layout')

@section('title', 'Список новых тем - @parent')

@section('content')
    <h1>Список новых тем</h1>
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
            Форум: <a href="/forum/<?=$data['forum_id']?>"><?=$data['title']?></a><br />
            Автор: <?=nickname($data['author'])?> / Посл.: <?=nickname($data['last_user'])?> (<?=date_fixed($data['last_time'])?>)
        </div>

    <?php endforeach; ?>

    <?php page_strnavigation('/forum/new/themes?', $config['forumtem'], $start, $total); ?>
@stop
