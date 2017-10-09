@extends('layout')

@section('title')
    Статьи пользователей
@stop

@section('content')

    <h1>Статьи пользователей</h1>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <i class="fa fa-pencil"></i>
            <b><a href="/blog/active/articles?user={{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} cтатей / {{ $data->comments }} комм.)<br>
        @endforeach

        {{ pagination($page) }}

        Всего пользователей: <b>{{ $page['total'] }}</b><br><br>
    @else
        {{ showError('Статей еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/blog">К блогам</a><br>
@stop
