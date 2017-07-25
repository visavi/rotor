@extends('layout')

@section('title')
    {{ $blog['title'] }} - @parent
@stop

@section('keywords')
    {{ $blog['tags'] }}
@stop

@section('description')
    {{ strip_str($blog['text']) }}
@stop

@section('content')

    <h1><?=$blog['title']?> <small>(Оценка: <?=format_num($blog['rating'])?>)</small></h1>

    <a href="/blog">Блоги</a> / <a href="/blog/<?=$blog['category_id']?>"><?=$blog['name']?></a> / <a href="/article/<?=$blog['id']?>/print">Печать</a> / <a href="/article/<?=$blog['id']?>/rss">RSS-лента</a>

    <?php if ($blog->getUser()->id == App::getUserId()): ?>
         / <a href="/blog/blog?act=editblog&amp;id=<?=$blog['id']?>">Изменить</a>
    <?php endif; ?>

    <br /><br />

    <?php if (is_admin()): ?>
        <br /> <a href="/admin/blog?act=editblog&amp;cid=<?=$blog['category_id']?>&amp;id=<?=$blog['id']?>">Редактировать</a> /
        <a href="/admin/blog?act=moveblog&amp;cid=<?=$blog['category_id']?>&amp;id=<?=$blog['id']?>">Переместить</a> /
        <a href="/admin/blog?act=delblog&amp;cid=<?=$blog['category_id']?>&amp;del=<?=$blog['id']?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
    <?php endif; ?>
    <hr />

    <?=$blog['text']?>

    <?php App::pagination($page); ?>

    Автор статьи: <?=profile($blog['user'])?> (<?=date_fixed($blog['created_at'])?>)<br />

    <i class="fa fa-tag"></i> <?=$tags?>

    <hr />

    Рейтинг: <a href="/blog/blog?act=vote&amp;id=<?=$blog['id']?>&amp;vote=down&amp;uid=<?=$_SESSION['token']?>"><i class="fa fa-thumbs-down"></i></a> <big><b><?=format_num($blog['rating'])?></b></big> <a href="/blog/blog?act=vote&amp;id=<?=$blog['id']?>&amp;vote=up&amp;uid=<?=$_SESSION['token']?>"><i class="fa fa-thumbs-up"></i></a><br /><br />

    <i class="fa fa-eye"></i> Просмотров: <?=$blog['visits']?><br />
    <i class="fa fa-comment"></i> <a href="/article/<?=$blog['id']?>/comments">Комментарии</a> (<?=$blog['comments']?>)
    <a href="/article/<?=$blog['id']?>/end">&raquo;</a><br /><br />
@stop
