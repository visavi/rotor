@extends('layout')

@section('title', $vote->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('votes.index') }}">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('votes.history') }}">{{ __('votes.archive_votes') }}</a></li>
            <li class="breadcrumb-item active">{{ $vote->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section mb-3 shadow">
    @foreach ($info['voted'] as $key => $data)
        <?php $proc = round(($data * 100) / $info['sum'], 1); ?>
        <?php $maxproc = round(($data * 100) / $info['max']); ?>

        <b>{{ $key }}</b> ({{ __('main.votes') }}: {{ $data }})<br>
        {{ progressBar($maxproc, $proc . '%') }}
    @endforeach
    </div>

    {{ __('votes.options') }}: <b>{{ count($info['voted']) }}</b><br>
    {{ __('votes.voted') }}: <b>{{ $vote->count }}</b><br><br>
@stop
