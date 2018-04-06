@extends('layout')

@section('title')
    Блоги - Список статей {{ $user->login }} (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Список статей {{ $user->login }}</h1>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/article/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>Автор: {!! profile($data->user) !!} ({{ dateFixed($data->created_at) }})<br>
                <i class="fa fa-comment"></i> <a href="/article/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/article/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего статей: <b>{{ $page->total }}</b><br>
    @else
        {!! showError('Статей еще нет!') !!}
    @endif
@stop
