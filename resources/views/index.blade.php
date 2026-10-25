@extends('layout')

@section('title', setting('logos'))

@section('content')
    @hook('advertIndexTop')

    @if ($homepage)
        {{ $homepage() }}
    @else
        <div id="feed-container">
            {{ $feed->getFeed() }}
        </div>
        <div id="feed-sentinel"></div>
    @endif

    @hook('advertIndexBottom')
@stop
