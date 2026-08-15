@use('App\Services\FeedService')
@use('App\Support\Hook')
@extends('layout')

@section('title', setting('logos'))

@section('content')
    @hook('advertIndexTop')

    @if(Hook::has('homepageView'))
        @hook('homepageView')
    @else
        <div id="feed-container">
            {{ (new FeedService())->getFeed() }}
        </div>
        <div id="feed-sentinel"></div>
    @endif

    @hook('advertIndexBottom')
@stop
