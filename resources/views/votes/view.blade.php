@extends('layout')

@section('title', $vote->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item active">{{ $vote->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($vote->topic->id)
        <div class="mb-3">
            {{ __('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a>
        </div>
    @endif

    @if (empty($show) && (empty($vote->poll) && getUser()))
        <div class="section-form mb-3 shadow">
            <form action="/votes/{{ $vote->id }}" method="post">
                @csrf
                @foreach ($vote->answers as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer['id'] }}"> {{ $answer['answer'] }}</label><br>
                @endforeach
                <button class="btn btn-primary">{{ __('votes.vote') }}</button>
            </form>
        </div>

        {{ __('votes.voted') }}: <b>{{ $vote->count }}</b><br><br>
        <i class="fa fa-history"></i> <a href="/votes/{{ $vote->id }}?show=true">{{ __('votes.results') }}</a><br>

    @else
        <div class="section mb-3 shadow">
            @foreach ($info['voted'] as $key => $data)
                <?php $proc = round(($data * 100) / $info['sum'], 1); ?>
                <?php $maxproc = round(($data * 100) / $info['max']); ?>

                <b>{{ $key }}</b> ({{ __('main.votes') }}: {{ $data }})<br>
                {!! progressBar($maxproc, $proc . '%') !!}
            @endforeach
        </div>

        {{ __('votes.voted') }}: <b>{{ $vote->count }}</b><br><br>

        @if (! empty($show))
            <i class="fa fa-chart-bar"></i> <a href="/votes/{{ $vote->id }}">{{ __('votes.vote') }}</a><br>
        @endif
        <i class="fa fa-users"></i> <a href="/votes/voters/{{ $vote->id }}">{{ __('votes.voted') }}</a><br>
    @endif
@stop
