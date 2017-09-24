@extends('layout')

@section('title')
    Галерея сайта (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Галерея сайта</h1>

<?php

$links = [
    ['url' => '/gallery/album/'.getUser('login'), 'label' => 'Мои альбом', 'show' => getUser()],
    ['url' => '/gallery/comments/'.getUser('login'), 'label' => 'Мои комментарии', 'show' => getUser()],
    ['url' => '/gallery/albums', 'label' => 'Все альбомы'],
    ['url' => '/gallery/comments', 'label' => 'Все комментарии'],
    ['url' => '/admin/gallery?page='.$page['current'], 'label' => 'Управление', 'show' => isAdmin()],
];
?>
    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li class="breadcrumb-item"><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>
    @if ($photos->isNotEmpty())
        @foreach ($photos as $data)

            <div class="b"><i class="fa fa-picture-o"></i>
                <b><a href="/gallery/{{ $data->id }}">{{ $data->title }}</a></b>
                ({{ formatFileSize(HOME.'/uploads/pictures/'.$data->link) }}) (Рейтинг: {!! formatNum($data->rating) !!})
            </div>

            <div>
                <a href="/gallery/{{ $data->id }}">{!! resizeImage('uploads/pictures/', $data->link, setting('previewsize'), ['alt' => $data->title]) !!}</a><br>

                @if ($data->text)
                    {!! bbCode($data->text) !!}<br>
                @endif

                Добавлено: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                <a href="/gallery/{{ $data->id }}/comments">Комментарии</a> ({{ $data->comments }})
                <a href="/gallery/{{ $data->id }}/end">&raquo;</a>
            </div>
        @endforeach

        {{ pagination($page) }}

        Всего фотографий: <b>{{ $page['total'] }}</b><br><br>

    @else
        {{ showError('Фотографий нет, будь первым!') }}
    @endif

    <?php
    $links = [
        ['url' => '/gallery/top', 'label' => 'Топ фото'],
        ['url' => '/gallery/create', 'label' => 'Добавить фото'],
    ];
    ?>
    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li class="breadcrumb-item"><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
        <?php endforeach; ?>
    </ol>
@stop
