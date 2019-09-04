@extends('layout')

@section('title')
    {{ __('votes.voted') }} {{ $vote->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/votes">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item"><a href="/votes/{{ $vote->id }}">{{ $vote->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.voted') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-chart-bar"></i> {{ __('main.votes') }}: {{ $vote->count }}<br><br>

    @if ($voters->isNotEmpty())
        @foreach ($voters as $voter)
            {!! $voter->user->getGender() !!} {!! $voter->user->getProfile() !!} ({{ dateFixed($voter->created_at) }})<br>
        @endforeach
    @else
        {!! showError(__('votes.empty_voted')) !!}
    @endif
@stop
