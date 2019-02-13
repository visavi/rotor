@extends('layout')

@section('title')
    Пользовательская реклама
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/reklama/create">Разместить рекламу</a>
    </div><br>

    <h1>Пользовательская реклама</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item active">Пользовательская реклама</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($records->isNotEmpty())

        <form action="/admin/reklama/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($records as $data)
                <div class="b">
                    <i class="fa fa-check-circle"></i>
                    <b><a href="{{ $data->site }}">{{ $data->name }}</a></b> ({!! $data->user->getProfile() !!})

                    <div class="float-right">
                        <a href="/admin/reklama/edit/{{ $data->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>
                </div>

                Истекает: {{ dateFixed($data->deleted_at) }}<br>

                @if ($data->color)
                    Цвет: <span style="color:{{ $data->color }}">{{ $data->color }}</span>,
                @else
                    Цвет: нет,
                @endif

                @if ($data->bold)
                    Жирный текст: есть<br>
                @else
                    Жирный текст: нет<br>
                @endif
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего ссылок: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('В данный момент рекламных ссылок еще нет!') !!}
    @endif
@stop
