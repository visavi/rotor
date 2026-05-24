@use('App\Classes\Feed')
@use('App\Classes\Hook')
@extends('layout')

@section('title', setting('logos'))

@section('content')
    {!! Hook::call('advertIndexTop') !!}

    @if(Hook::has('homepageView'))
        {!! Hook::call('homepageView') !!}
    @else
        <div id="feed-container">{{ (new Feed())->getFeed() }}</div>
        <div id="feed-sentinel"></div>
    @endif

    {!! Hook::call('advertIndexBottom') !!}
@stop
