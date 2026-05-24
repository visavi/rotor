@use('App\Classes\Feed')
@use('App\Classes\Hook')
@extends('layout')

@section('title', setting('logos'))

@section('content')
    @hook('advertIndexTop')

    @if(Hook::has('homepageView'))
        @hook('homepageView')
    @else
        <div id="feed-container">
            {{ (new Feed())->getFeed() }}
        </div>
        <div id="feed-sentinel"></div>
    @endif

    @hook('advertIndexBottom')
@stop
