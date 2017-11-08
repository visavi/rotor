@extends('layout')

@section('title')
    История голосований (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>История голосований</h1>

    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-briefcase"></i>
                <b><a href="/votes/history/{{ $vote->id }}">{{ $vote->title }}</a></b>
            </div>
            <div>
                @if ($vote->topic)
                    Тема: <a href="/topic/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                Создано: {{ dateFixed($vote->created_at) }}<br>
                Всего голосов: {{ $vote->count }}<br>
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Голосований в архиве еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
@stop
