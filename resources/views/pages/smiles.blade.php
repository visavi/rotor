@extends('layout')

@section('title')
    Список смайлов
@stop

@section('content')

    <h1>Список смайлов</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Список смайлов</li>
        </ol>
    </nav>

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
