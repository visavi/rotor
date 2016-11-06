@extends('layout')

@section('title', 'Список сообщений '.e($user).' - @parent')

@section('content')

    <h1>Список сообщений {{ $user }}</h1>

    <?php foreach ($posts as $data): ?>
        <div class="post">
            <div class="b">

                <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['topics_id']?>/<?=$data['id']?>"><?=$data['title']?></a></b>

                <?php if (is_admin()): ?>
                    <a href="#" class="pull-right" onclick="return deletePost(this)" data-tid="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                <?php endif; ?>

            </div>
            <div>
                <?=bb_code($data['text'])?><br />

                Написал: <?=nickname($data['user'])?> <small>(<?=date_fixed($data['time'])?>)</small><br />

                <?php if (is_admin() || empty($config['anonymity'])): ?>
                    <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/forum/active/posts?user='.$user.'&amp;', $config['forumpost'], $start, $total); ?>
@stop
