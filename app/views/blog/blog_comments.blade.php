@extends('layout')

@section('title')
    {{ $blog['title'] }} - Комментарии - @parent
@stop

@section('content')
    <h1><a href="/article/<?=$blog['id']?>"><?=$blog['title']?></a></h1>

    <a href="/article/<?=$blog['id']?>/rss">RSS-лента</a><hr />

    <?php if ($is_admin): ?>
        <form action="/blog/blog?act=del&amp;id=<?=$blog['id']?>&amp;page=<?=$page['current']?>&amp;uid=<?=$_SESSION['token']?>" method="post">
    <?php endif; ?>

    @forelse ($comments as $data)

        <div class="b">
            <div class="img"><?=user_avatars($data['user'])?></div>

            <?php if ($is_admin): ?>
                <span class="imgright"><input type="checkbox" name="del[]" value="<?=$data['id']?>" /></span>
            <?php endif; ?>

            <b><?=profile($data['user'])?></b> <small>(<?=date_fixed($data['created_at'])?>)</small><br />
            <?=user_title($data['user'])?> <?=user_online($data['user'])?>
        </div>

            <?php if (is_user() && App::getUserId() != $data->getUser()->id): ?>
                <div class="right">
                    <a href="/blog/blog?act=reply&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?>&amp;page=<?=$page['current']?>">Отв</a> /
                    <a href="/blog/blog?act=quote&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?>&amp;page=<?=$page['current']?>">Цит</a> /
                    <noindex><a href="/blog/blog?act=spam&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?>&amp;page=<?=$page['current']?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы подтверждаете факт спама?')" rel="nofollow">Спам</a></noindex>
                </div>
            <?php endif; ?>

            <?php if (App::getUserId() == $data->getUser()->id && $data['created_at'] + 600 > SITETIME): ?>
                <div class="right">
                    <a href="/blog/blog?act=edit&amp;id=<?=$blog['id']?>&amp;pid=<?=$data['id']?>&amp;page=<?=$page['current']?>">Редактировать</a>
                </div>
            <?php endif; ?>

            <div>
                <?=App::bbCode($data['text'])?><br />

            <?php if (is_admin() || empty(App::setting('anonymity'))): ?>
                <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
            <?php endif; ?>

        </div>
    @empty
        {{ show_error('Нет сообщений') }}
    @endforelse


    <?php if ($is_admin): ?>
        <span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>
    <?php endif; ?>
    <hr />

    @if (is_user())

    <div class="form">
        <form action="/blog/blog?act=add&amp;id=<?=$blog['id']?>&amp;uid=<?=$_SESSION['token']?>" method="post">
            <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />
            <input type="submit" value="Написать" />
        </form>
    </div><br />

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><br /><br />

    @else
        <?= show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо'); ?>
    @endif

<?php
    App::pagination($page);

    App::view('includes/back', ['link' => '/article/'.$blog['id'], 'title' => 'Вернуться']);
    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);
?>
@stop
