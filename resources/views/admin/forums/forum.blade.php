@extends('layout')

@section('title')
    {{ $forum->title }} ({{ __('main.page_num', ['page' => $topics->currentPage()]) }})
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/forums/create?fid={{ $forum->id }}">{{ __('forums.create_topic') }}</a>
    </div><br>

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
            <li class="breadcrumb-item"><a href="/forums/{{ $forum->id  }}?page={{ $topics->currentPage() }}">{{ __('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        @foreach ($topics as $topic)
            <div class="b" id="topic_{{ $topic->id }}">

                <div class="float-right">
                    <a href="/admin/topics/edit/{{ $topic->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/topics/move/{{ $topic->id }}" title="{{ __('main.move') }}"><i class="fa fa-arrows-alt text-muted"></i></a>
                    <a href="/admin/topics/delete/{{ $topic->id }}?page={{ $topics->currentPage() }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('forums.confirm_delete_topic') }}')" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                </div>

                <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                <b><a href="/admin/topics/{{ $topic->id }}">{{ $topic->title }}</a></b> ({{ $topic->count_posts }})
            </div>
            <div>
                @if ($topic->lastPost)
                    {!! $topic->pagination('/admin/topics') !!}
                    {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }} ({{ dateFixed($topic->lastPost->created_at) }})
                @endif
            </div>
        @endforeach
    @elseif ($forum->closed)
        {!! showError(__('forums.closed_forum')) !!}
    @else
        {!! showError(__('forums.empty_topics')) !!}
    @endif

    {{ $topics->links('app/_paginator') }}
@stop
