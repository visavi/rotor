@extends('layout')

@section('title')
    Топ популярных тем - @parent
@stop

@section('content')

    <h1>Топ популярных тем</h1>

    <a href="/forum">Форум</a>

    @if ($topics->isNotEmpty())
        @foreach ($topics as $data)
            <div class="b">
                <i class="fa {{ $data->getIcon() }} text-muted"></i>
                <b><a href="/topic/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({{ $data['posts'] }})
            </div>
            <div>
                {{ $data->pagination() }}
                Автор: {{ $data->user->login }}<br>
                Сообщение: {{ $data->lastPost->user->login }} ({{ dateFixed($data->lastPost->created_at) }})
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Созданных тем еще нет!') }}
    @endif
@stop
