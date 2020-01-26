@extends('layout')

@section('title')
    {{ __('index.forums') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/forums/create">{{ __('forums.create_topic') }}</a>
        </div>
    @endif

    <h1>{{ __('index.forums') }} {{ setting('title') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.forums') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('ads/_forum')

    @if (getUser())
        {{ __('main.my') }}: <a href="/forums/active/topics">{{ __('forums.topics') }}</a>, <a href="/forums/active/posts">{{ __('forums.posts') }}</a>, <a href="/forums/bookmarks">{{ __('forums.bookmarks') }}</a> /
    @endif

    {{ __('main.new') }}: <a href="/topics">{{ __('forums.topics') }}</a>, <a href="/posts">{{ __('forums.posts') }}</a>
    <hr/>

    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/forums/{{ $forum->id }}">{{ $forum->title }}</a></b>
                ({{ $forum->count_topics + $forum->children->sum('count_topics') }}/{{ $forum->count_posts + $forum->children->sum('count_posts') }})

                @if ($forum->description)
                    <p><small>{{ $forum->description }}</small></p>
                @endif
            </div>

            <div>
                @if ($forum->children->isNotEmpty())
                    @foreach ($forum->children as $child)
                        <i class="fa fa-copy text-muted"></i> <b><a href="/forums/{{ $child->id }}">{{ $child->title }}</a></b>
                        ({{ $child->count_topics }}/{{ $child->count_posts }})<br/>
                    @endforeach
                @endif

                @if ($forum->lastTopic->lastPost->id)
                        {{ __('forums.topic') }}: <a href="/topics/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
                    <br/>
                        {{ __('forums.post') }}: {{ $forum->lastTopic->lastPost->user->getName() }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                @else
                    {{ __('forums.empty_topics') }}
                @endif
            </div>
        @endforeach
    @else
        {!! showError(__('forums.empty_forums')) !!}
    @endif

    <br/>
    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ __('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ __('forums.top_posts') }}</a> /
    <a href="/forums/search">{{ __('main.search') }}</a> /
    <a href="/forums/rss">{{ __('main.rss') }}</a><br/>
@stop
