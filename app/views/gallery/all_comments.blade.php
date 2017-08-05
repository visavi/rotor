@extends('layout')

@section('title')
    Список всех комментариев (Стр. {{ $page['current']}}) - @parent
@stop

@section('content')

    <h1>Список всех комментариев</h1>

    @if ($comments)
        @foreach ($comments as $data)

            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/gallery/{{ $data['relate_id']}}/{{ $data['id'] }}/comment">{{ $data['title'] }}</a></b>
            </div>

            <div>
                {!! App::bbCode($data['text']) !!}<br />
                Написал: <b>{!! profile($data['user']) !!}</b> <small>({{ date_fixed($data['created_at']) }})</small><br />

            @if (is_admin())
                <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
            @endif

            </div>
        @endforeach

        {{ App::pagination($page) }}

    @else
        {{ show_error('Комментариев еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />
@stop
