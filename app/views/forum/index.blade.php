@extends('layout')

@section('title', 'Форум - @parent')

@section('content')

    <h1>Форум {{ App::setting('title') }}</h1>
    @if (is_user())
        Мои: <a href="/forum/active/themes">темы</a>, <a href="/forum/active/posts">сообщения</a>, <a href="/forum/bookmark">закладки</a> /
    @endif

    Новые: <a href="/forum/new/themes">темы</a>, <a href="/forum/new/posts">сообщения</a>
    <hr/>

    @foreach($forums[0] AS $key => $data)
        <div class="b">
            <i class="fa fa-file-text-o fa-lg text-muted"></i>
            <b><a href="/forum/{{ $data['id'] }}">{{ $data['title'] }}</a></b>
            ({{ $data['topics'] }}/{{ $data['posts'] }})

            @if (!empty($data['desc']))
                <br/>
                <small>{{ $data['desc'] }}</small>
            @endif

        </div>

        <div>
            @if (isset($forums[$key]))
                @foreach($forums[$key] as $datasub)
                    <i class="fa fa-files-o text-muted"></i> <b><a href="/forum/{{ $datasub['id'] }}">{{ $datasub['title'] }}</a></b>
                    ({{ $datasub['topics'] }}/{{ $datasub['posts'] }})<br/>
                @endforeach
            @endif

            @if ($data['last_id'] > 0)
                Тема: <a href="/topic/{{ $data['last_id'] }}/end">{{ $data['last_themes'] }}</a>
                <br/>
                Сообщение: {{ nickname($data['last_user']) }} ({{ date_fixed($data['last_time']) }})
            @else
                Темы еще не созданы!
            @endif

        </div>
    @endforeach

    <br/><a href="/rules">Правила</a> / <a href="/forum/top/themes">Топ тем</a> / <a href="/forum/search">Поиск</a><br/>
@stop
