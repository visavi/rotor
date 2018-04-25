@extends('layout')

@section('title')
    История авторизаций
@stop

@section('content')

    <h1>История авторизаций</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item active">История авторизаций</li>
        </ol>
    </nav>

    @if ($logins->isNotEmpty())
        @foreach($logins as $data)
            <div class="b">
                @if ($data->type)
                    <i class="fa fa-sign-in-alt"></i> <b>Авторизация</b>
                @else
                    <i class="fa fa-sync"></i> <b>Автовход</b>
                @endif

                <small>({{ dateFixed($data->created_at) }})</small>
            </div>
            <div>
                <span class="data">
                    Browser: {{ $data->brow }} /
                    IP: {{ $data->ip }}
                </span>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('История авторизаций отсутствует') !!}
    @endif
@stop
