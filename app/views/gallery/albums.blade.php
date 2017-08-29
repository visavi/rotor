@extends('layout')

@section('title')
    Альбомы пользователей (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Альбомы пользователей</h1>

    @if ($albums->isNotEmpty())
        @foreach ($albums as $data)

            <i class="fa fa-picture-o"></i>
            <b><a href="/gallery/album/{{ $data->login }}">{{ $data->login }}</a></b> ({{ $data['cnt'] }} фото / {{ $data['comments'] }} комм.)<br>

        @endforeach

        {{ App::pagination($page) }}

        Всего альбомов: <b>{{ $page['total'] }}</b><br><br>
    @else
        {{ App::showError('Альбомов еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
