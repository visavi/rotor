@extends('layout')

@section('title', $forum->title . ' (' . __('main.page_num', ['page' => $topics->currentPage()]) . ')')

@section('header')
    <div class="float-right">
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

            @if ($forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $forum->parent->id }}">{{ $forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $forum->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        @foreach ($topics as $topic)
            <div class="section mb-3 shadow" id="topic_{{ $topic->id }}">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                            <a href="/admin/topics/{{ $topic->id }}">{{ $topic->title }}</a> ({{ $topic->count_posts }})
                        </div>
                    </div>
                    <div class="text-right">
                        <a href="/admin/topics/edit/{{ $topic->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/topics/move/{{ $topic->id }}" title="{{ __('main.move') }}"><i class="fa fa-arrows-alt text-muted"></i></a>
                        <a href="/admin/topics/delete/{{ $topic->id }}?page={{ $topics->currentPage() }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('forums.confirm_delete_topic') }}')" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                    </div>
                </div>
                <div class="section-content">
                    @if ($topic->lastPost)
                        {!! $topic->pagination('/admin/topics') !!}
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
