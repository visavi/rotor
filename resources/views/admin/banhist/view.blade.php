@extends('layout')

@section('title')
    История банов {{ $user->login }}
@stop

@section('content')

    <h1>История банов {{ $user->login }}</h1>

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

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/banhist">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
