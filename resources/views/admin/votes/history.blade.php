@extends('layout')

@section('title')
    Архив голосований
@stop

@section('content')

    <h1>Архив голосований</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/votes">Голосования</a></li>
            <li class="breadcrumb-item active">Архив голосований</li>
        </ol>
    </nav>

    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-chart-bar"></i>
                <b><a href="/votes/history/{{ $vote['id'] }}">{{ $vote->title }}</a></b>

                <div class="float-right">
                    <a href="/admin/votes/edit/{{ $vote->id }}" title="Редактировать"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/votes/close/{{ $vote->id }}?token={{ $_SESSION['token'] }}" title="Открыть"><i class="fa fa-unlock text-muted"></i></a>

                    @if (isAdmin('boss'))
                        <a href="/admin/votes/delete/{{ $vote->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить голосование?')" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                </div>

                @endif
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
        {!! showError('Голосований в архиве еще нет!') !!}
    @endif
@stop
