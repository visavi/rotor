@extends('layout')

@section('title')
    Альбомы пользователей (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Альбомы пользователей</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/gallery">Галерея</a></li>
            <li class="breadcrumb-item active">Альбомы пользователей</li>
        </ol>
    </nav>

    @if ($albums->isNotEmpty())
        @foreach ($albums as $data)

            <i class="fa fa-image"></i>
            <b><a href="/gallery/album/{{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} фото / {{ $data->count_comments }} комм.)<br>

        @endforeach

        {!! pagination($page) !!}

        Всего альбомов: <b>{{ $page['total'] }}</b><br><br>
    @else
        {!! showError('Альбомов еще нет!') !!}
    @endif
@stop
