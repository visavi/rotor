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
        @foreach ($voters as $voter)
            <div class="section mb-3 shadow">
                {{ $voter->user->getGender() }} {{ $voter->user->getProfile() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($voter->created_at) }}</small><br>
                {{ __('main.vote') }}: {{ $voter->vote }}
            </div>
        @endforeach
    @else
        {{ showError(__('votes.empty_voted')) }}
    @endif
@stop
