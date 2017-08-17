@extends('layout')

@section('title')
    Блоги - Список разделов - @parent
@stop

@section('content')

    <h1>Блоги</h1>

    <?php if (is_user()): ?>
        Мои: <a href="/blog/active/blogs">статьи</a>, <a href="/blog/active/comments">комментарии</a> /
    <?php endif; ?>

    Новые: <a href="/blog/new/blogs">статьи</a>, <a href="/blog/new/comments">комментарии</a><hr>

    <?php foreach($blogs as $key => $data): ?>
        <i class="fa fa-folder-open"></i> <b><a href="/blog/<?=$data['id']?>"><?=$data['name']?></a></b>

        <?php if ($data->new): ?>
            (<?=$data->count ?>/+<?=$data->new->count ?>)<br>
        <?php else: ?>
            (<?= $data->count ?>)<br>
        <?php endif; ?>
    <?php endforeach; ?>

    <br>
    <a href="/blog/top">Топ статей</a> /
    <a href="/blog/tags">Облако тегов</a> /
    <a href="/blog/search">Поиск</a> /
    <a href="/blog/blog?act=blogs">Все статьи</a> /
    <a href="/blog/blog?act=new">Написать</a> /
    <a href="/blog/rss">RSS</a><br>
@stop
