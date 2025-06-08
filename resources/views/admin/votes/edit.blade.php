@extends('layout')

@section('title', __('votes.edit_vote'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/votes">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item"><a href="/votes/{{ $vote->id }}">{{ $vote->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.edit_vote') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('votes/_form')

    <p class="text-muted fst-italic">{{ __('votes.hint_text') }}</p>
@stop
