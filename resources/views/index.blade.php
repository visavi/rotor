@extends('layout')

@section('title')
    {{ setting('logos') }}
@stop

@section('header')
    @include('ads/_top')
    <h1>{{ setting('title') }}</h1>
@stop

@section('content')
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/news">{{ trans('index.news') }}</a> ({{ statsNewsDate() }})<br> {{ lastNews() }}

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b><a href="/pages/recent">{{ trans('index.communication') }}</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a class="index" href="/guestbooks">{{ trans('index.guestbooks') }}</a> ({{  statsGuestbook() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/photos">{{ trans('index.photos') }}</a> ({{ statsPhotos() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/votes">{{ trans('index.votes') }}</a> ({{ statVotes()}})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/offers">{{ trans('index.offers') }}</a> ({{ statsOffers() }})<br>

    <div class="b">
        <i class="fab fa-forumbee fa-lg text-muted"></i>
        <b><a href="/forums">{{ trans('index.forums') }}</a></b> ({{ statsForum() }})
    </div>
    {{ recentTopics() }}

    <div class="b">
        <i class="fa fa-download fa-lg text-muted"></i> <b><a href="/loads">{{ trans('index.loads') }}</a></b> ({{ statsLoad() }})
    </div>
    {{ recentFiles() }}

    <div class="b">
        <i class="fa fa-globe fa-lg text-muted"></i>
        <b><a href="/blogs">{{ trans('index.blogs') }}</a></b> ({{ statsBlog() }})
    </div>
    {{ recentBlogs() }}

    <div class="b">
        <i class="fa fa-list-alt fa-lg text-muted"></i>
        <b><a href="/boards">{{ trans('index.boards') }}</a></b> ({{ statsBoard() }})
    </div>
    {{ recentBoards() }}

    <div class="b">
        <i class="fa fa-cog fa-lg text-muted"></i>
        <b><a href="/pages">{{ trans('index.pages') }}</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/files/docs">{{ trans('index.docs') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/search">{{ trans('index.search') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/mails">{{ trans('index.mails') }}</a><br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/users">{{ trans('index.users') }}</a> ({{  statsUsers() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/administrators">{{ trans('index.administrators') }}</a> ({{ statsAdmins() }})<br>

    <div class="b">
        <i class="fa fa-chart-line fa-lg text-muted"></i> <b>{{ trans('index.courses') }}</b>
    </div>
    {!! getCourses() !!}

    <div class="b">
        <i class="fa fa-calendar-alt fa-lg text-muted"></i> <b>{{ trans('index.calendar') }}</b>
    </div>
    {!! getCalendar() !!}

    @include('ads/_bottom')
@stop
