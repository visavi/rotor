@extends('layout')

@section('title')
    {{ setting('logos') }}
@stop

@section('header')
    @include('ads/_top')
    <h1>{{ setting('title') }}</h1>
@stop

@section('content')

    @if (!getUser())
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-body main-register">
                        <h5 class="card-title mb-3">Сообщество разработчиков Visavi.net</h5>
                        <p class="card-text">
                            Зарегистрируйте бесплатный аккаут сегодня!<br>
                            Вы сможете общаться на форуме, вести блог и загружать файлы
                        </p>
                        <a class="btn btn-success mt-2" href="/register">Присоединиться бесплатно</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-body main-register">
                    <h5 class="card-title mb-3"><a href="/news">{{ __('index.news') }}</a> <span class="badge badge-pill badge-light">{{ statsNewsDate() }}</span></h5>
                    <p class="card-text">
                        {{ lastNews() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <i class="far fa-circle fa-lg text-muted"></i> <a href="/news">{{ __('index.news') }}</a> ({{ statsNewsDate() }})<br> {{ lastNews() }}

    <div class="b">
        <i class="fa fa-comment fa-lg text-muted"></i> <b><a href="/pages/recent">{{ __('index.communication') }}</a></b>
    </div>
    <i class="far fa-circle fa-lg text-muted"></i> <a class="index" href="/guestbooks">{{ __('index.guestbooks') }}</a> ({{ statsGuestbook() }})<br>
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
    {{ recentBlogs() }}

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
