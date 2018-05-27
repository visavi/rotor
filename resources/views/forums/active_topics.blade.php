@extends('layout')

@section('title')
    Форум - Список тем {{ $user->login }} (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Список тем {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item active">Список тем {{ $user->login }}</li>
        </ol>
    </nav>

    @foreach ($topics as $data)
        <div class="b">
            <i class="fa {{ $data->getIcon() }} text-muted"></i>
            <b><a href="/topics/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_posts }})
        </div>

        <div>
            {!! $data->pagination() !!}
            Форум: <a href="/forums/{{ $data->forum->id }}">{{ $data->forum->title }}</a><br>
            Автор: {!! profile($data->user, null, false) !!} / Посл.: {!! profile($data->lastPost->user, null, false) !!} ({{ dateFixed($data->lastPost->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
