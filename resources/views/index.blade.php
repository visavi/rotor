@extends('layout')

@section('title', setting('logos'))

@section('header')
    <h1>{{ setting('title') }}</h1>
    <p>{{ setting('logos') }}</p>
@stop

@section('content')
    @include('ads/_top')

    @if (1 === 2)
        @include('widgets._classic');
    @else
        {{ (new \App\Classes\Feed())->getFeed() }}
    @endif

    @include('ads/_bottom')
@stop
