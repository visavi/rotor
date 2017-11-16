@extends('layout')

@section('title')
    Список всех статей {{ $user->login }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Список всех статей {{ $user->login }}</h1>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil"></i>
                <b><a href="/article/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>Автор: {!! profile($data->user) !!} ({{ dateFixed($data->time) }})<br>
                <i class="fa fa-comment"></i> <a href="/article/{{ $data->id }}/comments">Комментарии</a> ({{ $data->comments }})
                <a href="/article/{{ $data->id }}/end">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего статей: <b>{{ $page['total'] }}</b><br>
    @else
        {!! showError('Статей еще нет!') !!}
    @endif
@stop
