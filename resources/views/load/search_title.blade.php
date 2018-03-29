@extends('layout')

@section('title')
    Поиск запроса {{ $find }}
@stop

@section('content')
    <h1>Поиск запроса {{ $find }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
            <li class="breadcrumb-item"><a href="/load/search">Поиск</a></li>
            <li class="breadcrumb-item active">Поиск запроса</li>
        </ol>
    </nav>

    Найдено совпадений в названии: <b>{{ $page['total'] }}</b><br><br>

    @foreach ($downs as $data)
        <?php $filesize = $data->link ? formatFileSize(UPLOADS.'/files/'.$data->link) : 0; ?>

        <div class="b">
            <i class="fa fa-file"></i>
            <b><a href="/down/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $filesize }})
        </div>

        <div>
            Категория: <a href="/load/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
            Скачиваний: {{ $data->loads }}<br>

            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            Рейтинг: <b>{{ $rating }}</b> (Голосов: {{ $data->rated }})<br>
            Добавил: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
