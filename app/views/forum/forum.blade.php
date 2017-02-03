@extends('layout')

@section('title')
    {{ $forum['title'] }} (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>{{ $forum['title'] }}</h1>

    <a href="/forum">Форум</a>

    @if ($forum->parent)
        / <a href="/forum/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a>
    @endif

    / {{ $forum['title'] }}

    @if (is_admin())
        / <a href="/admin/forum?act=forum&amp;fid=<?= $forum->id ?>&amp;page=<?=$page['current']?>">Управление</a>
    @endif

    @if (is_user() && empty($forum['closed']))
        <div class="pull-right">
            <a class="btn btn-success" href="/forum/create?fid={{ $forum->id }}">Создать тему</a>
        </div>
    @endif

    <hr />

    <?php if ($forum->children && $page['current'] == 1): ?>
        <div class="act">

        <?php foreach ($forum->children as $child): ?>

            <div class="b"><i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/<?=$child['id']?>"><?=$child['title']?></a></b> (<?= $child->countTopic->count ?>/<?= $child->countPost->count ?>)</div>

            <?= var_dump($child) ?>
            <?php if ($child->lastTopic): ?>
                <div>
                    Тема: <a href="/topic/<?= $child->lastTopic->id ?>/end"><?= $child->lastTopic->title ?></a><br />
                    @if ($child->lastTopic->lastPost)
                        Сообщение: <?=$child->lastTopic->lastPost->getUser()->login ?> (<?=date_fixed($child->lastTopic->lastPost->time)?>)
                    @endif
                </div>
            <?php else: ?>
                <div>Темы еще не созданы!</div>
            <?php endif; ?>
        <?php endforeach; ?>

        </div>
        <hr />
    <?php endif; ?>

    @if ($topics)
        <?php foreach ($topics as $topic): ?>
            <div class="b" id="topic_<?=$topic['id']?>">
                <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                <b><a href="/topic/<?=$topic['id']?>"><?=$topic['title']?></a></b> ({{ $topic->countPost->count }})
            </div>
            <div>
                @if ($topic->lastPost)
                    <?= Forum::pagination($topic)?>
                    Сообщение: <?=nickname($topic->lastPost->getUser()->login)?> (<?=date_fixed($topic->lastPost->time)?>)
                @endif
            </div>
        <?php endforeach; ?>

        <?php App::pagination($page) ?>

    @elseif ($forums['closed'])
        <?=show_error('В данном разделе запрещено создавать темы!')?>
    @else
        <?=show_error('Тем еще нет, будь первым!')?>
    @endif


    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/search?fid=<?= $forum->id ?>">Поиск</a><br />
@stop
