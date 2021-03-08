@extends('layout')

@section('title', __('votes.voted') . ' ' . $vote->title)

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
    <div class="mb-3">
        <i class="fa fa-chart-bar"></i> {{ __('main.votes') }}: {{ $vote->count }}
    </div>

    @if ($voters->isNotEmpty())
        <div class="section mb-3 shadow">
        @foreach ($voters as $voter)
            {{ $voter->user->getGender() }} {{ $voter->user->getProfile() }} ({{ dateFixed($voter->created_at) }})
        </div>
        @endforeach
    @else
        {{ showError(__('votes.empty_voted')) }}
    @endif
@stop
