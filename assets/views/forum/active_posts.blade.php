@extends('layout')

@section('title', 'Список сообщений '.e($user).' - @parent')

@section('content')

    <h1>Список сообщений {{ $user }}</h1>

    <?php foreach ($posts as $data): ?>
        <div class="b">

            <i class="fa fa-file-text-o"></i> <b><a href="/topic/<?=$data['posts_topics_id']?>/<?=$data['posts_id']?>"><?=$data['topics_title']?></a></b>

            <?php /* TODO if (is_admin()): */?><!--
                — <a href="/forum/active.php?act=del&amp;id=<?/*=$data['posts_id']*/?>&amp;uz=<?/*=$user*/?>&amp;start=<?/*=$start*/?>&amp;uid=<?/*=$_SESSION['token']*/?>">Удалить</a>
            --><?php /*endif;*/ ?>

        </div>
        <div>
            <?=bb_code($data['posts_text'])?><br />

            Написал: <?=nickname($data['posts_user'])?> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />

            <?php if (is_admin() || empty($config['anonymity'])): ?>
                <span class="data">(<?=$data['posts_brow']?>, <?=$data['posts_ip']?>)</span>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/forum/active/posts?user='.$user.'&amp;', $config['forumpost'], $start, $total); ?>
@stop
