@extends('layout')

@section('title')
    Список смайлов - @parent
@stop

@section('content')

    <h1>Список смайлов</h1>

    @if ($smiles)
        @foreach($smiles as $smile)
            <img src="/uploads/smiles/{{ $smile['name'] }}" alt=""> — <b>{{ $smile['code'] }}</b><br>
        @endforeach

        {{  App::pagination($page) }}

        Всего cмайлов: <b>{{ $page['total'] }}</b><br><br>
    @else
        {{  show_error('Смайлы не найдены!') }}
    @endif
@stop
