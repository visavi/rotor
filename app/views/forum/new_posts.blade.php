@extends('layout')

@section('title', 'Список новых сообщений - @parent')

@section('content')
    <h1>Список новых сообщений</h1>

    <?php foreach ($posts as $data): ?>
        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['topics_id']?>/<?=$data['id']?>"><?=$data['title']?></a></b>
            (<?=$data['posts']?>)
        </div>
        <div>
            <?=bb_code($data['text'])?><br />

            Написал: <?=nickname($data['user'])?> <?=user_online($data['user'])?> <small>(<?=date_fixed($data['time'])?>)</small><br />

            <?php if (is_admin() || empty($config['anonymity'])): ?>
                <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/forum/new/posts?', $config['forumpost'], $start, $total); ?>
@stop
