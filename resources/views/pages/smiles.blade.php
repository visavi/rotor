@extends('layout')

@section('title')
    Список смайлов
@stop

@section('content')

    <h1>Список смайлов</h1>

    @if ($smiles)
        @foreach ($smiles as $smile)
            <img src="/uploads/smiles/{{ $smile['name'] }}" alt=""> — <b>{{ $smile['code'] }}</b><br>
        @endforeach

        {!! pagination($page) !!}

        Всего cмайлов: <b>{{ $page['total'] }}</b><br><br>
    @else
        {!! showError('Смайлы не найдены!') !!}
    @endif
@stop
