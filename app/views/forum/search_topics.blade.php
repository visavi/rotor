@extends('layout')

@section('title', 'Поиск запроса '.e($find).' - @parent')

@section('content')

    <h3>Поиск запроса <?=$find?></h3>

    <p>Найдено совпадений в темах: <?=$page['total']?></p>

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
            Сообщение: <?=nickname($data['last_user'])?> (<?=date_fixed($data['last_time'])?>)
        </div>
    <?php endforeach; ?>

    <?php App::pagination($page) ?>
@stop
