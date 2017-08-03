@extends('layout')

@section('title')
    Топ популярных постов - @parent
@stop

@section('content')
    <h1>Топ популярных постов</h1>

    <a href="/forum">Форум</a>

    <?php foreach ($posts as $data): ?>
        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['topic_id']?>/<?=$data['id']?>"><?= $data->getTopic()->title ?></a></b>
            (Рейтинг: <?= $data->rating ?>)
        </div>
        <div>
            <?=App::bbCode($data['text'])?><br />

            Написал: <?= $data->getUser()->login ?> <?=user_online($data->user)?> <small>(<?=date_fixed($data['created_at'])?>)</small><br />

            <?php if (is_admin()): ?>
                <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

    <?php App::pagination($page) ?>
@stop
