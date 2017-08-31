@extends('layout')

@section('title')
    Список тем {{ $user->login }} - @parent
@stop

@section('content')

    <h1>Список тем {{ $user->login }}</h1>

    <a href="/forum">Форум</a>

    @foreach ($topics as $data)
        <div class="b">
            <i class="fa {{ $data->getIcon() }} text-muted"></i>
            <b><a href="/topic/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({{ $data['posts'] }})
        </div>

        <div>
            {{ Forum::pagination($data) }}
            Форум: <a href="/forum/{{ $data->getForum()->id }}">{{ $data->getForum()->title }}</a><br>
            Автор: {{ $data->getUser()->login }} / Посл.: {{ $data->getLastPost()->getUser()->login }} ({{ date_fixed($data->getLastPost()->created_at) }})
        </div>
    @endforeach

    {{ pagination($page) }}
@stop
