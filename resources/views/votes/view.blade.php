@extends('layout')

@section('title')
    {{ $vote->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ trans('votes.title') }}</a></li>
            <li class="breadcrumb-item active">{{ $vote->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($vote->topic->id)
        {{ trans('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br><br>
    @endif

    @if (empty($show) && (empty($vote->poll) && getUser()))
        <form action="/votes/{{ $vote->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach($vote->answers as $answer)
                <label><input name="poll" type="radio" value="{{ $answer['id'] }}"> {{ $answer['answer'] }}</label><br>
            @endforeach
            <br>
            <button class="btn btn-sm btn-primary">{{ trans('votes.vote') }}</button>
        </form><br>

        {{ trans('votes.voted') }}: <b>{{ $vote->count }}</b><br><br>
        <i class="fa fa-history"></i> <a href="/votes/{{ $vote->id }}?show=true">{{ trans('votes.results') }}</a><br>

    @else
        @foreach ($info['voted'] as $key => $data)
            <?php $proc = round(($data * 100) / $info['sum'], 1); ?>
            <?php $maxproc = round(($data * 100) / $info['max']); ?>

            <b>{{ $key }}</b> ({{ trans('main.votes') }}: {{ $data }})<br>
            {!! progressBar($maxproc, $proc.'%') !!}
        @endforeach

        {{ trans('votes.voted') }}: <b>{{ $vote->count }}</b><br><br>

        @if (! empty($show))
            <i class="fa fa-chart-bar"></i> <a href="/votes/{{ $vote->id }}">{{ trans('votes.vote') }}</a><br>
        @endif
        <i class="fa fa-users"></i> <a href="/votes/voters/{{ $vote->id }}">{{ trans('votes.voted') }}</a><br>
    @endif
@stop
