@extends('layout')

@section('title')
    Галерея сайта (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Галерея сайта</h1>

<?php

$links = [
    ['url' => '/gallery/album/'.App::user('login'), 'label' => 'Мои фото', 'show' => is_user()],
    ['url' => '/gallery/comments?act=comments', 'label' => 'Мои комментарии', 'show' => is_user()],
    ['url' => '/gallery/album', 'label' => 'Все альбомы'],
    ['url' => '/gallery/comments', 'label' => 'Все комментарии'],
    ['url' => '/admin/gallery?page='.$page['current'], 'label' => 'Управление', 'show' => is_admin()],
];
?>
    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>
    <?php if ($page['total'] > 0): ?>
        <?php foreach($photos as $data): ?>

            <div class="b"><i class="fa fa-picture-o"></i>
                <b><a href="/gallery/<?= $data['id'] ?>"><?= $data['title'] ?></a></b>
                (<?= read_file(HOME.'/uploads/pictures/'.$data['link']) ?>) (Рейтинг: <?= format_num($data['rating']) ?>)
            </div>

            <div>
                <a href="/gallery/<?= $data['id'] ?>"><?= resize_image('uploads/pictures/', $data['link'], App::setting('previewsize'), ['alt' => $data['title']]) ?></a><br />

                <?php if (!empty($data['text'])): ?>
                    <?= App::bbCode($data['text']) ?><br />
                <?php endif; ?>

                Добавлено: <?= profile($data->user) ?> (<?= date_fixed($data['created_at']) ?>)<br />
                <a href="/gallery/<?= $data['id'] ?>/comments">Комментарии</a> (<?= $data['comments'] ?>)
                <a href="/gallery/<?= $data['id'] ?>/end">&raquo;</a>
            </div>
        <?php endforeach; ?>

        <?php App::pagination($page) ?>

        Всего фотографий: <b><?= $total ?></b><br /><br />

    <?php else: ?>
        <?= show_error('Фотографий нет, будь первым!'); ?>
    <?php endif; ?>

    <?php
    $links = [
        ['url' => '/gallery/top', 'label' => 'Топ фото'],
        ['url' => '/gallery/create', 'label' => 'Добавить фото'],
    ];
    ?>
    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>

@stop
