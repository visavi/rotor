@extends('layout')

@section('title', $forum->title . ' (' . __('main.page_num', ['page' => $topics->currentPage()]) . ')')

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/forums/create?fid={{ $forum->id }}">{{ __('forums.create_topic') }}</a>
        <a class="btn btn-light" href="/forums/{{ $forum->id  }}?page={{ $topics->currentPage() }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ $forum->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ __('index.forums') }}</a></li>

            @foreach ($forum->getParents() as $parent)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $parent->title }}</li>
                @else
                    <li class="breadcrumb-item"><a href="/admin/forums/{{ $parent->id }}">{{ $parent->title }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->onFirstPage() && $forum->children->isNotEmpty())
        @foreach ($forum->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file-alt fa-lg text-muted"></i>
                            <a href="/admin/forums/{{ $child->id }}">{{ $child->title }}</a>
                            ({{ $child->count_topics }}/{{ $child->count_posts }})
                        </div>
                    </div>

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="/admin/forums/edit/{{ $forum->id }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/forums/delete/{{ $forum->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>

                @if ($child->lastTopic->id)
                    <div class="section-content">
                        {{ __('forums.topic') }}: <a href="/admin/topics/end/{{ $child->lastTopic->id }}">{{ $child->lastTopic->title }}</a><br>
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
                            <a href="/admin/topics/{{ $topic->id }}">{{ $topic->title }}</a> ({{ $topic->getCountPosts() }})
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="/admin/topics/edit/{{ $topic->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/topics/move/{{ $topic->id }}" title="{{ __('main.move') }}"><i class="fa fa-arrows-alt text-muted"></i></a>
                        <a href="/admin/topics/delete/{{ $topic->id }}?page={{ $topics->currentPage() }}&amp;_token={{ csrf_token() }}" onclick="return confirm('{{ __('forums.confirm_delete_topic') }}')" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                    </div>
                </div>
                <div class="section-content">
                    @if ($topic->lastPost)
                        {{ $topic->pagination('/admin/topics') }}
                        {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
                    @endif
                </div>
            </div>
        @endforeach
    @elseif ($forum->closed)
        {{ showError(__('forums.closed_forum')) }}
    @else
        {{ showError(__('forums.empty_topics')) }}
    @endif

    {{ $topics->links() }}
@stop
