@extends('layout')

@section('title')
    Топ популярных тем
@stop

@section('content')

    <h1>Топ популярных тем</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item active">Топ популярных тем</li>
        </ol>
    </nav>

    @if ($topics->isNotEmpty())
        @foreach ($topics as $data)
            <div class="b">
                <i class="fa {{ $data->getIcon() }} text-muted"></i>
                <b><a href="/topics/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_posts }})
            </div>
            <div>
                {!! $data->pagination() !!}
                Автор: {{ $data->user->login }}<br>
                Сообщение: {{ $data->lastPost->user->login }} ({{ dateFixed($data->lastPost->created_at) }})
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Созданных тем еще нет!') !!}
    @endif
@stop
