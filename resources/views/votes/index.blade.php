@extends('layout')

@section('title')
    Голосования ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/votes/create">Создать голосование</a><br>
        </div><br>
    @endif

    <h1>Голосования</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Голосования</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-chart-bar"></i>
                <b><a href="/votes/{{ $vote['id'] }}">{{ $vote->title }}</a></b>
            </div>
            <div>
                @if ($vote->topic->id)
                    Тема: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                Создано: {{ dateFixed($vote->created_at) }}<br>
                Всего голосов: {{ $vote->count }}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Открытых голосований еще нет!') !!}
    @endif

    <i class="fa fa-briefcase"></i> <a href="/votes/history">Архив голосований</a><br>
@stop
