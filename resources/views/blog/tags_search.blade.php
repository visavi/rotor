@extends('layout')

@section('title')
    Поиск по тегам
@stop

@section('content')
    <h1>Поиск по тегам</h1>

    <h3>Поиск запроса &quot;<?= $tag ?>&quot; в метках</h3>
    Найдено совпадений: <b><?= $page['total'] ?></b><br>

    <?php foreach($blogs as $data): ?>

        <div class="b">
            <i class="fa fa-pencil"></i>
            <b><a href="/article/<?=$data['id']?>"><?=$data['title']?></a></b> (<?=formatNum($data['rating'])?>)
        </div>

        <div>
            Категория: <a href="/blog/<?=$data['category_id']?>"><?=$data['name']?></a><br>
            Просмотров: <?=$data['visits']?><br>
            Метки: <?=$data['tags']?><br>
            Автор: <?=profile($data['user'])?>  (<?=dateFixed($data['created_at'])?>)
        </div>
    <?php endforeach; ?>

    {{ pagination($page) }}

    <i class="fa fa-arrow-circle-up"></i> <a href="/blog">К блогам</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/blog/tags">Облако</a><br>
@stop
