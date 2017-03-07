@extends('layout')

@section('title')
    Поиск запроса {{ $find }} - @parent
@stop

@section('content')

    <h3>Поиск запроса <?=$find?></h3>

    <p>Найдено совпадений в сообщениях: <?=$page['total']?></p>

    <?php foreach ($posts as $post): ?>

        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$post['topic_id']?>/<?=$post['id']?>"><?= $post->getTopic()->title ?></a></b>
        </div>

        <div><?=App::bbCode($post['text'])?><br />
            Написал: <?=profile($post->user)?> <?=user_online($post->user)?> <small>(<?=date_fixed($post['created_at'])?>)</small><br />
        </div>

    <?php endforeach; ?>

	<?php App::pagination($page) ?>
@stop
