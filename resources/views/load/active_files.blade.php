@extends('layout')

@section('title')
    Загрузки - Список файлов {{ $user->login }} (Стр. {{ $page['current'] }})
@stop

@section('content')
    <h1>Файлы {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
            <li class="breadcrumb-item active">Файлы {{ $user->login }}</li>
        </ol>
    </nav>

    <?php /*
    echo '<i class="fa fa-book"></i> ';
    echo '<a href="/load/add">Публикация</a> / ';
    echo '<a href="/load/add?act=waiting">Ожидающие</a> / ';
    echo '<b>Проверенные</b><hr>';
*/ ?>

    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)
            <?php $filesize = $down->link ? formatFileSize(UPLOADS.'/files/'.$down->link) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/down/{{ $down->id }}">{{ $down->title }}</a></b> ({{ $filesize }})
            </div>
            <div>
                Категория: <a href="/load/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                Скачиваний: {{ $down->loads }}<br>

                <?php $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0; ?>

                Рейтинг: <b>{{ $rating }}</b> (Голосов: {{ $down->rated }})<br>
                Автор: {!! profile($down->user) !!} ({{ dateFixed($down->created_at) }})
            </div>

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных файлов еще нет!') !!}
    @endif
@stop
