@extends('layout')

@section('title')
    Список новых тем
@stop

@section('content')
    <h1>Список новых тем</h1>

    <a href="/forum">Форум</a>

    @foreach ($topics as $data)
        <div class="b">
            <i class="fa {{ $data->getIcon() }} text-muted"></i>
            <b><a href="/topic/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({{ $data['posts'] }})
        </div>

        <div>
            {{ $data->pagination() }}
            Форум: <a href="/forum/{{  $data->forum->id }}">{{  $data->forum->title }}</a><br>
            Автор: {{ $data->user->login }} / Посл.: {{ $data->lastPost->user->login }} ({{ dateFixed($data['created_at']) }})
        </div>

    @endforeach

    {{ pagination($page) }}
@stop
