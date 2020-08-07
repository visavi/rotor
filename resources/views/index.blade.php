@extends('layout')

@section('title')
    {{ setting('logos') }}
@stop

@section('header')
    @include('ads/_top')
    <h1>{{ setting('title') }}</h1>
@stop

@section('content')
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/news">{{ __('index.news') }}</a> ({{ statsNewsDate() }})<br> {{ lastNews() }}

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b><a href="/pages/recent">{{ __('index.communication') }}</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a class="index" href="/guestbook">{{ __('index.guestbook') }}</a> ({{ statsGuestbook() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/photos">{{ __('index.photos') }}</a> ({{ statsPhotos() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/votes">{{ __('index.votes') }}</a> ({{ statVotes()}})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/offers">{{ __('index.offers') }}</a> ({{ statsOffers() }})<br>

    <div class="b">
        <i class="fab fa-forumbee fa-lg text-muted"></i>
        <b><a href="/forums">{{ __('index.forums') }}</a></b> ({{ statsForum() }})
    </div>
    {{ recentTopics() }}

    <div class="b">
        <i class="fa fa-download fa-lg text-muted"></i> <b><a href="/loads">{{ __('index.loads') }}</a></b> ({{ statsLoad() }})
    </div>
    {{ recentDowns() }}

    <div class="b">
        <i class="fa fa-globe fa-lg text-muted"></i>
        <b><a href="/blogs">{{ __('index.blogs') }}</a></b> ({{ statsBlog() }})
    </div>
    {{ recentArticles() }}

    <div class="b">
        <i class="fa fa-list-alt fa-lg text-muted"></i>
        <b><a href="/boards">{{ __('index.boards') }}</a></b> ({{ statsBoard() }})
    </div>
    {{ recentBoards() }}

    <div class="b">
        <i class="fa fa-cog fa-lg text-muted"></i>
        <b><a href="/pages">{{ __('index.pages') }}</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/files/docs">{{ __('index.docs') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/search">{{ __('index.search') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/mails">{{ __('index.mails') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/users">{{ __('index.users') }}</a> ({{  statsUsers() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/administrators">{{ __('index.administrators') }}</a> ({{ statsAdmins() }})<br>

    <div class="b">
        <i class="fa fa-chart-line fa-lg text-muted"></i> <b>{{ __('index.courses') }}</b>
    </div>
    {!! getCourses() !!}

    <div class="b">
        <i class="fa fa-calendar-alt fa-lg text-muted"></i> <b>{{ __('index.calendar') }}</b>
    </div>
    {!! getCalendar() !!}

    @include('ads/_bottom')
@stop
