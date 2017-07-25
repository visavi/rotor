@extends('layout')

@section('title')
    Поиск по тегам - @parent
@stop

@section('content')
    <h1>Поиск по тегам</h1>

    <h3>Поиск запроса &quot;<?= $tag ?>&quot; в метках</h3>
    Найдено совпадений: <b><?= $page['total'] ?></b><br />

    <?php foreach($blogs as $data): ?>

        <div class="b">
            <i class="fa fa-pencil"></i>
            <b><a href="/article/<?=$data['id']?>"><?=$data['title']?></a></b> (<?=format_num($data['rating'])?>)
        </div>

        <div>
            Категория: <a href="/blog/<?=$data['category_id']?>"><?=$data['name']?></a><br />
            Просмотров: <?=$data['visits']?><br />
            Метки: <?=$data['tags']?><br />
            Автор: <?=profile($data['user'])?>  (<?=date_fixed($data['created_at'])?>)
        </div>
    <?php endforeach; ?>

    {{ App::pagination($page) }}

    <?php
    App::view('includes/back', ['link' => '/blog/tags', 'title' => 'Облако']);
    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
    ?>
@stop
