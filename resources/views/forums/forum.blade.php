@extends('layout')

@section('title', $forum->title, ' (' . __('main.page_num', ['page' => $topics->currentPage()]) . ')')

@section('header')
    <div class="float-right">
        @if (getUser())
            @if (! $forum->closed)
                <a class="btn btn-success" href="/forums/create?fid={{ $forum->id }}">{{ __('forums.create_topic') }}</a>
            @endif

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/forums/{{  $forum->id  }}?page={{ $topics->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ $forum->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>

            @if ($forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $forum->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->onFirstPage() && $forum->children->isNotEmpty())
        @foreach ($forum->children as $child)
            <div class="section mb-3 shadow border-left border-info">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file-alt fa-lg text-muted"></i>
                            <a href="/forums/{{ $child->id }}">{{ $child->title }}</a>
                        </div>
                    </div>

                    <div class="text-right">
                        <b>{{ $child->count_topics }}/{{ $child->count_posts }}</b>
                    </div>
                </div>

                @if ($child->lastTopic->id)
                    <div class="section-content">
                        {{ __('forums.topic') }}: <a href="/topics/end/{{ $child->lastTopic->id }}">{{ $child->lastTopic->title }}</a><br>
                        @if ($child->lastTopic->lastPost->id)
                            {{ __('forums.post') }}: {{ $child->lastTopic->lastPost->user->getName() }} ({{ dateFixed($child->lastTopic->lastPost->created_at) }})
                        @endif
                    </div>
                @else
                    <div>{{ __('forums.empty_topics') }}</div>
                @endif
            </div>
        @endforeach
        <hr>
    @endif

    @if ($topics->isNotEmpty())
        @foreach ($topics as $topic)
            <div class="section mb-3 shadow" id="topic_{{ $topic->id }}">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                            <a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a>
                        </div>
                    </div>
                    <div class="text-right">
                        <b>{{ $topic->getCountPosts() }}</b>
                    </div>
                </div>

                @if ($topic->lastPost)
                    <div class="section-content">
                        {{ $topic->pagination() }}
                        {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
                    </div>
                @endif
            </div>
        @endforeach
    @elseif ($forum->closed)
        {{ showError(__('forums.closed_forum')) }}
    @else
        {{ showError(__('forums.empty_topics')) }}
    @endif

    {{ $topics->links() }}

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ __('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ __('forums.top_posts') }}</a> /
    <a href="/forums/search?fid={{ $forum->id }}">{{ __('main.search') }}</a><br>
@stop
