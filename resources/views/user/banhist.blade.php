@extends('layout')

@section('title')
    История банов {{ $user->login }}
@stop

@section('content')

    <h1>История банов {{ $user->login }}</h1>

    @if ($banhist->isNotEmpty())

        @foreach ($banhist as $data)
        <div class="b">
            <div class="img">{!! userAvatar($data->user) !!}</div>
            <b>{!! profile($data->user) !!}</b> ({{ dateFixed($data->created_at) }})</div>

        <div>
            @if ($data->type)
                Причина: {!! bbCode($data->reason) !!}<br>
                Срок: {{ formatTime($data->term) }}<br>
            @endif

            @switch($data->type)
                @case(1)
                <span style="color:#ff0000">Забанил</span>
                @break

                @case(2)
                <span style="color:#ffa500">Изменил</span>
                @break

                @default
                <span style="color:#00cc00">Разбанил</span>
            @endswitch

            {!! profile($data->sendUser) !!}<br>

            </div>
        @endforeach

        {{ pagination($page) }}

        Всего действий: <b>{{ $page['total'] }}</b><br><br>
    @else
        {{ showError('В истории еще ничего нет!') }}
    @endif


    <i class="fa fa-arrow-circle-left"></i> <a href="/user/{{ $user->login }}">В анкету</a><br>
@stop
