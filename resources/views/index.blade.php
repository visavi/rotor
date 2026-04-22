@use('App\Classes\Feed')
@extends('layout')

@section('title', setting('logos'))

@section('content')
    @include('ads/_top')

    @if (setting('homepage_view') === 'feed')
        <div id="feed-container">{{ (new Feed())->getFeed() }}</div>
        <div id="feed-sentinel"></div>
    @else
        @include('widgets/_classic')
    @endif

    @include('ads/_bottom')
@stop
