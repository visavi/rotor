@extends('layout')

@section('title')
    Уведомления
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item"><a href="/messages">Приватные сообщения</a></li>
            <li class="breadcrumb-item active">Уведомления</li>
        </ol>
    </nav>

    <h1>Уведомления</h1>

    @if ($messages->isNotEmpty())

        @foreach ($messages as $data)
            <div class="post">
                <div class="b">
                    <div class="img">
                        {!! $user->getAvatar() !!}
                    </div>

                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}
                    </div>

                    <b>Система</b>

                    @unless ($data->read)
                        <br><span class="badge badge-info">Новое</span>
                    @endunless
                </div>
                <div class="message">{!! bbCode($data->text) !!}</div>
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError('Уведомления отсутствуют!') !!}
    @endif

    Уведомлений: <b>{{ $page->total }}</b><br><br>

    @if ($page->total)
        <i class="fa fa-times"></i> <a href="/messages/delete/0?token={{ $_SESSION['token'] }}">Удалить переписку</a><br>
    @endif
    <i class="fa fa-search"></i> <a href="/searchusers">Поиск пользователей</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">Контакт</a> / <a href="/ignores">Игнор</a><br>
@stop
