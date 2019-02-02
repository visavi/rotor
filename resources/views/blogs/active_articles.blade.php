@extends('layout')

@section('title')
    Блоги - Список статей {{ $user->login }} (Стр. {{ $page->current }})
@stop

@section('header')
    <h1>Список статей {{ $user->login }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>
            <li class="breadcrumb-item active">Список статей {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>Автор: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего статей: <b>{{ $page->total }}</b><br>
    @else
        {!! showError('Статей еще нет!') !!}
    @endif
@stop
