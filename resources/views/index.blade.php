@use('App\Classes\Feed')
@extends('layout')

@section('title', setting('logos'))

@section('header')
    <h1>{{ setting('title') }}</h1>
    <p>{{ setting('logos') }}</p>
@stop

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
