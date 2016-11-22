@extends('layout')

@section('title', 'Поиск запроса '.e($find).' - @parent')

@section('content')

    <h3>Поиск запроса <?=$find?></h3>

    <p>Найдено совпадений в сообщениях: <?=$page['total']?></p>

    <?php foreach ($posts as $data): ?>

        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['topic_id']?>/<?=$data['id']?>"><?=$data['title']?></a></b>
        </div>

        <div><?=App::bbCode($data['text'])?><br />
            Написал: <?=profile($data['user'])?> <?=user_online($data['user'])?> <small>(<?=date_fixed($data['time'])?>)</small><br />
        </div>

    <?php endforeach; ?>

	<?php App::pagination($page) ?>
@stop
