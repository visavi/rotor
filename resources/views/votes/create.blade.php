@extends('layout')

@section('title', __('votes.create_vote'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('votes.index') }}">{{ __('index.votes') }}</a></li>
            <li class="breadcrumb-item active">{{ __('votes.create_vote') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('votes/_form')
@stop
