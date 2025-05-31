@extends('layout')

@section('title', __('index.forums'))

@section('header')
    <div class="float-end">
        @if (getUser())
            <a class="btn btn-success" href="/forums/create">{{ __('forums.create_topic') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/forums"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ __('index.forums') }}</h1>
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

    <div class="my-3 py-2 border-bottom">
        @if (getUser())
            {{ __('main.my') }}: <a href="/forums/active/topics">{{ __('forums.topics') }}</a>, <a href="/forums/active/posts">{{ __('forums.posts') }}</a>, <a href="/forums/bookmarks">{{ __('forums.bookmarks') }}</a> /
        @endif

        {{ __('main.new') }}: <a href="/topics">{{ __('forums.topics') }}</a>, <a href="/posts">{{ __('forums.posts') }}</a>
    </div>

    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center position-relative">
                    <div class="flex-grow-1">
                        <i class="fa fa-file-alt fa-lg text-muted"></i>
                        <a href="/forums/{{ $forum->id }}" class="section-title position-relative">{{ $forum->title }}</a>
                        <span class="badge bg-adaptive">{{ formatShortNum($forum->count_topics + $forum->children->sum('count_topics')) }}/{{ formatShortNum($forum->count_posts + $forum->children->sum('count_posts')) }}</span>

                        @if ($forum->description)
                            <div class="section-description text-muted small">{{ $forum->description }}</div>
                        @endif
                    </div>

                    @if ($forum->children->isNotEmpty())
                        <div>
                            <a data-bs-toggle="collapse" class="stretched-link" href="#section_{{ $forum->id }}">
                                <i class="treeview-indicator fas fa-angle-down"></i>
                            </a>
                        </div>
                    @endif
                </div>
                <div>
                    @if ($forum->children->isNotEmpty())
                        @php $forum->children->load('children'); @endphp
                        <div class="collapse" id="section_{{ $forum->id }}">
                            <div class="section-content border-top p-2">
                                @foreach ($forum->children as $child)
                                    <div>
                                        <i class="fas fa-angle-right"></i> <a href="/forums/{{ $child->id }}">{{ $child->title }}</a>
                                        <span class="badge bg-adaptive">{{ $child->count_topics + $child->children->sum('count_topics') }}/{{ $child->count_posts + $child->children->sum('count_posts') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="section-body border-top">
                    @if ($forum->lastTopic->lastPost->id)
                        {{ __('forums.topic') }}: <a href="/topics/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
                        <br>
                        {{ __('forums.post') }}: {{ $forum->lastTopic->lastPost->user->getName() }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                    @else
                        {{ __('forums.empty_topics') }}
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.empty_forums')) }}
    @endif

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ __('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ __('forums.top_posts') }}</a> /
    <a href="/forums/rss">{{ __('main.rss') }}</a><br>
@stop
