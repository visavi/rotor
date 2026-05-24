@use('App\Classes\Feed')
@use('App\Classes\Hook')
@extends('layout')

@section('title', setting('logos'))

@section('content')
    @include('ads/_top')

    @if(Hook::has('homepageView'))
        {!! Hook::call('homepageView') !!}
    @else
        <div id="feed-container">{{ (new Feed())->getFeed() }}</div>
        <div id="feed-sentinel"></div>
    @endif

    @include('ads/_bottom')
@stop
