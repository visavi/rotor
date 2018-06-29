@extends('layout')

@section('title')
    {{ $vote->title }}
@stop

@section('content')

    <h1>{{ $vote->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">Голосования</a></li>
            <li class="breadcrumb-item"><a href="/votes/history">История голосований</a></li>
            <li class="breadcrumb-item active">{{ $vote->title }}</li>
        </ol>
    </nav>

    @foreach ($info['voted'] as $key => $data)
        <?php $proc = round(($data * 100) / $info['sum'], 1); ?>
        <?php $maxproc = round(($data * 100) / $info['max']); ?>

        <b>{{ $key }}</b> (Голосов: {{ $data }})<br>
        {!! progressBar($maxproc, $proc.'%') !!}
    @endforeach

    Вариантов: <b>{{ count($vote->voted) }}</b><br>
    Проголосовало: <b>{{ $vote->count }}</b><br><br>
@stop
