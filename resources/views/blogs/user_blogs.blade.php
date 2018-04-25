@extends('layout')

@section('title')
    Статьи пользователей
@stop

@section('content')

    <h1>Статьи пользователей</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>
            <li class="breadcrumb-item active">Статьи пользователей</li>
        </ol>
    </nav>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/blogs/active/articles?user={{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} cтатей / {{ $data->count_comments }} комм.)<br>
        @endforeach

        {!! pagination($page) !!}

        Всего пользователей: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Статей еще нет!') !!}
    @endif
@stop
