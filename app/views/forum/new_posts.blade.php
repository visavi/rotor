@extends('layout')

@section('title', 'Список новых сообщений - @parent')

@section('content')
    <h1>Список новых сообщений</h1>

    <?php foreach ($posts as $data): ?>
        <div class="b">
            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['posts_topics_id']?>/<?=$data['posts_id']?>"><?=$data['title']?></a></b>
            (<?=$data['posts']?>)
        </div>
        <div>
            <?=bb_code($data['posts_text'])?><br />

            Написал: <?=nickname($data['posts_user'])?> <?=user_online($data['posts_user'])?> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />

            <?php if (is_admin() || empty($config['anonymity'])): ?>
                <span class="data">(<?=$data['posts_brow']?>, <?=$data['posts_ip']?>)</span>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/forum/new/posts?', $config['forumpost'], $start, $total); ?>
@stop
