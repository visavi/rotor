@extends('layout')

@section('title')
    {{ trans('votes.voted') }} {{ $vote->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ trans('index.votes') }}</a></li>
            <li class="breadcrumb-item"><a href="/votes/{{ $vote->id }}">{{ $vote->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('votes.voted') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-chart-bar"></i> {{ trans('main.votes') }}: {{ $vote->count }}<br><br>

    @if ($voters->isNotEmpty())
        @foreach ($voters as $voter)
            {!! $voter->user->getGender() !!} {!! $voter->user->getProfile() !!} ({{ dateFixed($voter->created_at) }})<br>
        @endforeach
    @else
        {!! showError(trans('votes.empty_voted')) !!}
    @endif
@stop
