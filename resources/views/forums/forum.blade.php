@extends('layout')

@section('title')
    {{ $forum->title }} ({{ trans('common.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    @if (! $forum->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/forums/create?fid={{ $forum->id }}">{{ trans('forums.create_topic') }}</a>
        </div><br>
    @endif

    <h1>{{ $forum->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>

            @if ($forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $forum->title }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/forums/{{  $forum->id  }}?page={{ $page->current }}">{{ trans('common.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($page->current === 1 && $forum->children->isNotEmpty())
        <div class="act">

        @foreach ($forum->children as $child)

            <div class="b"><i class="fa fa-file-alt fa-lg text-muted"></i>
            <b><a href="/forums/{{ $child->id }}">{{ $child->title }}</a></b> ({{ $child->count_topics }}/{{ $child->count_posts }})</div>

            @if ($child->lastTopic->id)
                <div>
                    {{ trans('forums.topic') }}: <a href="/topics/end/{{ $child->lastTopic->id }}">{{ $child->lastTopic->title }}</a><br>
                    @if ($child->lastTopic->lastPost->id)
                        {{ trans('forums.post') }}: {{ $child->lastTopic->lastPost->user->getName() }} ({{ dateFixed($child->lastTopic->lastPost->created_at) }})
                    @endif
                </div>
            @else
                <div>{{ trans('forums.empty_topic') }}</div>
            @endif
        @endforeach

        </div>
        <hr>
    @endif

    @if ($topics->isNotEmpty())
        @foreach ($topics as $topic)
            <div class="b" id="topic_{{ $topic->id }}">
                <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})
            </div>
            <div>
                @if ($topic->lastPost)
                    {!! $topic->pagination() !!}
                    {{ trans('forums.post') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}

    @elseif ($forum->closed)
        {!! showError(trans('forums.closed_forum')) !!}
    @else
        {!! showError(trans('forums.empty_topic')) !!}
    @endif

    <a href="/rules">{{ trans('common.rules') }}</a> /
    <a href="/forums/top/topics">{{ trans('forums.top_topics') }}</a> /
    <a href="/forums/top/posts">{{ trans('forums.top_posts') }}</a> /
    <a href="/forums/search?fid={{ $forum->id }}">{{ trans('common.search') }}</a><br>
@stop
