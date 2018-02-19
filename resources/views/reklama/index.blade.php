@extends('layout')

@section('title')
    Реклама на сайте
@stop

@section('content')

    <div class="float-right">
        <a class="btn btn-success" href="/reklama/create">Разместить рекламу</a>
    </div>

    <h1>Реклама на сайте</h1><br>

    @if ($adverts->isNotEmpty())
        @foreach($adverts as $data)
            <div class="b">
                <i class="fa fa-check-circle"></i>
                <b><a href="{{ $data->site }}">{{ $data->name }}</a></b> ({!! profile($data->user) !!})
            </div>

            Истекает: {{ dateFixed($data->deleted_at) }}<br>

            @if ($data->color)
                Цвет: <span style="color:{{ $data->color }}">{{ $data->color }}</span>,
            @else
                Цвет: нет,
            @endif

            @if ($data->bold)
                Жирность: есть<br>
            @else
                Жирность: нет<br>
            @endif
        @endforeach

        {!! pagination($page) !!}

        Всего ссылок: <b>{{ $page['total'] }}</b><br><br>
    @else
        {!! showError('В данный момент рекламных ссылок еще нет!') !!}
    @endif
@stop
