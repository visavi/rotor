@extends('layout')

@section('title')
    Топ популярных постов
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item active">Топ популярных постов</li>
        </ol>
    </nav>

    <h1>Топ популярных постов</h1>

    Период:
    <?php $active = ($period === 1) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=1" class="badge badge-{{ $active }}">Сутки</a>

    <?php $active = ($period === 7) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=7" class="badge badge-{{ $active }}">Неделя</a>

    <?php $active = ($period === 30) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=30" class="badge badge-{{ $active }}">Месяц</a>

    <?php $active = ($period === 365) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=365" class="badge badge-{{ $active }}">Год</a>

    <?php $active = (empty($period)) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts" class="badge badge-{{ $active }}">За все время</a>
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="b">
                <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
                (Рейтинг: {{ $data->rating }})
            </div>
            <div>
                {!! bbCode($data->text) !!}<br>

                Написал: {{ $data->user->login }} <small>({{ dateFixed($data->created_at) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif

            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Сообщений еще нет!') !!}
    @endif
@stop
