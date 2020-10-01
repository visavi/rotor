@extends('layout')

@section('title')
    {{ setting('logos') }}
@stop

@section('header')
    @include('ads/_top')
    <h1>{{ setting('title') }}</h1>
    <p>{{ setting('logos') }}</p>
@stop

@section('content')
    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="far fa-circle fa-lg text-muted"></i>
            <a href="/news" class="">{{ __('index.news') }}</a> ({{ statsNewsDate() }})
        </div>
        {!! lastNews() !!}
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-comment fa-lg text-muted"></i>
            <a href="/pages/recent">{{ __('index.communication') }}</a>
        </div>
        <div class="section-body my-1 py-1">
            <i class="far fa-circle fa-lg text-muted"></i> <a class="index" href="/guestbook">{{ __('index.guestbook') }}</a> ({{ statsGuestbook() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/photos">{{ __('index.photos') }}</a> ({{ statsPhotos() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/votes">{{ __('index.votes') }}</a> ({{ statVotes()}})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/offers">{{ __('index.offers') }}</a> ({{ statsOffers() }})<br>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fab fa-forumbee fa-lg text-muted"></i>
            <a href="/forums">{{ __('index.forums') }}</a> ({{ statsForum() }})
        </div>
        {!! recentTopics() !!}
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-download fa-lg text-muted"></i>
            <a href="/loads">{{ __('index.loads') }}</a> ({{ statsLoad() }})
        </div>
        {!! recentDowns() !!}
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-globe fa-lg text-muted"></i>
            <a href="/blogs">{{ __('index.blogs') }}</a> ({{ statsBlog() }})
        </div>
        {!! recentArticles() !!}
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-list-alt fa-lg text-muted"></i>
            <a href="/boards">{{ __('index.boards') }}</a> ({{ statsBoard() }})
        </div>
        {!! recentBoards() !!}
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-cog fa-lg text-muted"></i>
            <a href="/pages">{{ __('index.pages') }}</a>
        </div>
        <div class="section-body my-1 py-1">
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/files/docs">{{ __('index.docs') }}</a><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/search">{{ __('index.search') }}</a><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/mails">{{ __('index.mails') }}</a><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/users">{{ __('index.users') }}</a> ({{  statsUsers() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/administrators">{{ __('index.administrators') }}</a> ({{ statsAdmins() }})
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-chart-line fa-lg text-muted"></i>
                    {{ __('index.courses') }}
                </div>
                <div class="section-body my-1 py-1">
                    {!! getCourses() !!}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-calendar-alt fa-lg text-muted"></i>
                    {{ __('index.calendar') }}
                </div>
                <div class="section-body my-1 py-1">
                    {!! getCalendar() !!}
                </div>
            </div>
        </div>
    </div>
    @include('ads/_bottom')
@stop
