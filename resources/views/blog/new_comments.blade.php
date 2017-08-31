@extends('layout')

@section('title')
    Блоги - Новые комментарии (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Новые комментарии</h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/article/{{ $data['relate_id'] }}/comments">{{ $data['title'] }}</a></b> ({{ $data['comments'] }})
            </div>

            <div>
                {!! bbCode($data['text']) !!}<br>
                Написал: {!! profile($data['user']) !!} <small>({{ dateFixed($data['time']) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                @endif
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Комментарии не найдены!') }}
    @endif
@stop
