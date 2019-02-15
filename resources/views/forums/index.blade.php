@extends('layout')

@section('title')
    {{ trans('forums.title') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/forums/create">{{ trans('forums.create_topic') }}</a>
        </div><br>
    @endif

    <h1>{{ trans('forums.title') }} {{ setting('title') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('forums.forum') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('advert/_forum')

    @if (getUser())
        {{ trans('main.my') }}: <a href="/forums/active/topics">{{ trans('forums.topics') }}</a>, <a href="/forums/active/posts">{{ trans('forums.posts') }}</a>, <a href="/forums/bookmarks">{{ trans('forums.bookmarks') }}</a> /
    @endif

    {{ trans('main.new') }}: <a href="/topics">{{ trans('forums.topics') }}</a>, <a href="/posts">{{ trans('forums.posts') }}</a>
    <hr/>

    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/forums/{{ $forum->id }}">{{ $forum->title }}</a></b>
                ({{ $forum->count_topics }}/{{ $forum->count_posts }})

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
                        {{ trans('forums.topic') }}: <a href="/topics/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
                    <br/>
                        {{ trans('forums.post') }}: {{ $forum->lastTopic->lastPost->user->getName() }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                @else
                    {{ trans('forums.empty_topics') }}
                @endif
            </div>
        @endforeach
    @else
        {!! showError(trans('forums.empty_forums')) !!}
    @endif

    <br/>
    <a href="/rules">{{ trans('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ trans('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ trans('forums.top_posts') }}</a> /
    <a href="/forums/search">{{ trans('main.search') }}</a> /
    <a href="/forums/rss">{{ trans('main.rss') }}</a><br/>
@stop
