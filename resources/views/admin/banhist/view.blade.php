@extends('layout')

@section('title')
    Просмотр истории {{ $user->login }}
@stop

@section('content')

    <h1>Просмотр истории {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/banhist">История банов</a></li>
            <li class="breadcrumb-item active">Просмотр истории {{ $user->login }}</li>
        </ol>
    </nav>

    @if ($banhist->isNotEmpty())

        <form action="/admin/banhist/delete?user={{ $user->login }}&amp;page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($banhist as $data)
                <div class="b">

                    <div class="float-right">
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <div class="img">{!! userAvatar($data->user) !!}</div>
                    <b>{!! profile($data->user) !!}</b> ({{ dateFixed($data->created_at) }})
                </div>

                <div>
                    @if ($data->type !== 'unban')
                        Причина: {!! bbCode($data->reason) !!}<br>
                        Срок: {{ formatTime($data->term) }}<br>
                    @endif

                    {!! $data->getType() !!}: {!! profile($data->sendUser) !!}<br>

                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

    @else
        {!! showError('В истории еще ничего нет!') !!}
    @endif
@stop
