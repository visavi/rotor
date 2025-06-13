@extends('layout')

@section('title', $forum->title, ' (' . __('main.page_num', ['page' => $topics->currentPage()]) . ')')

@section('header')
    <div class="float-end">
        @if (getUser())
            @if (! $forum->closed)
                <a class="btn btn-success" href="{{ route('forums.create', ['fid' => $forum->id]) }}">{{ __('forums.create_topic') }}</a>
            @endif

            @if (isAdmin())
                <a class="btn btn-light" href="{{ route('admin.forums.forum', ['id' => $forum->id, 'page' => $topics->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ $forum->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>

            @foreach ($forum->getParents() as $parent)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $parent->title }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('forums.forum', ['id' => $parent->id]) }}">{{ $parent->title }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->onFirstPage() && $forum->children->isNotEmpty())
        @php $forum->children->load('children'); @endphp
        @foreach ($forum->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file-alt fa-lg text-muted"></i>
                            <a href="{{ route('forums.forum', ['id' => $child->id]) }}">{{ $child->title }}</a>
                        </div>
                    </div>

                    <div class="text-end">
                        <span class="badge bg-adaptive">{{ $child->count_topics + $child->children->sum('count_topics') }}/{{ $child->count_posts + $child->children->sum('count_posts') }}</span>
                    </div>
                </div>

                @if ($child->description)
                    <div class="section-description text-muted fst-italic small">{{ $child->description }}</div>
                @endif

                @if ($child->lastTopic->id)
                    <div class="section-content">
                        {{ __('forums.topic') }}: <a href="{{ route('topics.topic', ['id' => $child->lastTopic->id]) }}">{{ $child->lastTopic->title }}</a><br>
                        @if ($child->lastTopic->lastPost->id)
                            {{ __('forums.post') }}: {{ $child->lastTopic->lastPost->user->getName() }} <small class="section-date text-muted fst-italic">{{ dateFixed($child->lastTopic->lastPost->created_at) }}</small>
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
                            <a href="{{ route('topics.topic', ['id' => $topic->id]) }}">{{ $topic->title }}</a>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-adaptive">{{ $topic->getCountPosts() }}</span>
                    </div>
                </div>

                @if ($topic->lastPost)
                    <div class="section-content">
                        {{ $topic->pagination() }}
                        {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }} <small class="section-date text-muted fst-italic">{{ dateFixed($topic->lastPost->created_at) }}</small>
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
    <a href="{{ route('forums.top-topics') }}">{{ __('forums.top_topics') }}</a> /
    <a href="{{ route('forums.top-posts') }}">{{ __('forums.top_posts') }}</a><br>
@stop
