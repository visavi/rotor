@extends('layout')

@section('title')
    Список сообщений {{ $user }} - @parent
@stop

@section('content')

    <h1>Список сообщений {{ $user }}</h1>

    <a href="/forum">Форум</a>

    <?php foreach ($posts as $data): ?>
        <div class="post">
            <div class="b">

                <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['topic_id']?>/<?=$data['id']?>"><?=$data['title']?></a></b>

                <?php if (is_admin()): ?>
                    <a href="#" class="pull-right" onclick="return deletePost(this)" data-tid="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                <?php endif; ?>

            </div>
            <div>
                <?=App::bbCode($data['text'])?><br />

                Написал: <?=$data['user']?> <small>(<?=date_fixed($data['time'])?>)</small><br />

                <?php if (is_admin() || empty($config['anonymity'])): ?>
                    <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>

    <?php App::pagination($page) ?>
@stop
