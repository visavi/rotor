@extends('layout')

@section('title')
    Галерея сайта (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Галерея сайта</h1>

<?php

$links = [
    ['url' => '/gallery/album/'.App::user('login'), 'label' => 'Мои альбом', 'show' => is_user()],
    ['url' => '/gallery/comments/'.App::user('login'), 'label' => 'Мои комментарии', 'show' => is_user()],
    ['url' => '/gallery/albums', 'label' => 'Все альбомы'],
    ['url' => '/gallery/comments', 'label' => 'Все комментарии'],
    ['url' => '/admin/gallery?page='.$page['current'], 'label' => 'Управление', 'show' => is_admin()],
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
                <b><a href="/gallery/{{ $data['id'] }}">{{ $data['title'] }}</a></b>
                ({{ read_file(HOME.'/uploads/pictures/'.$data['link']) }}) (Рейтинг: {!! format_num($data['rating']) !!})
            </div>

            <div>
                <a href="/gallery/{{ $data['id'] }}">{!! resize_image('uploads/pictures/', $data['link'], Setting::get('previewsize'), ['alt' => $data['title']]) !!}</a><br>

                @if ($data['text'])
                    {!! App::bbCode($data['text']) !!}<br>
                @endif

                Добавлено: {!! profile($data->user) !!} ({{ date_fixed($data['created_at']) }})<br>
                <a href="/gallery/{{ $data['id'] }}/comments">Комментарии</a> ({{ $data['comments'] }})
                <a href="/gallery/{{ $data['id'] }}/end">&raquo;</a>
            </div>
        @endforeach

        {{ App::pagination($page) }}

        Всего фотографий: <b>{{ $page['total'] }}</b><br><br>

    @else
        {{ show_error('Фотографий нет, будь первым!') }}
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
