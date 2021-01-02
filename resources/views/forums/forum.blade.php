@extends('layout')

@section('title', $forum->title, ' (' . __('main.page_num', ['page' => $topics->currentPage()]) . ')')

@section('header')
    @if (! $forum->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/forums/create?fid={{ $forum->id }}">{{ __('forums.create_topic') }}</a>
        </div>
    @endif

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

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/forums/{{  $forum->id  }}?page={{ $topics->currentPage() }}">{{ __('main.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->onFirstPage() && $forum->children->isNotEmpty())
        @foreach ($forum->children as $child)
            <div class="section mb-3 shadow border-left border-info">
                <div class="section-title">
                    <i class="fa fa-file-alt fa-lg text-muted"></i>
                    <a href="/forums/{{ $child->id }}">{{ $child->title }}</a> ({{ $child->count_topics }}/{{ $child->count_posts }})
                </div>

                @if ($child->lastTopic->id)
                    <div>
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
                <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})

                @if ($topic->lastPost)
                    {!! $topic->pagination() !!}
                    {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
                @endif
            </div>
        @endforeach
    @elseif ($forum->closed)
        {!! showError(__('forums.closed_forum')) !!}
    @else
        {!! showError(__('forums.empty_topics')) !!}
    @endif

    {{ $topics->links() }}

    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/forums/top/topics">{{ __('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ __('forums.top_posts') }}</a> /
    <a href="/forums/search?fid={{ $forum->id }}">{{ __('main.search') }}</a><br>
@stop
