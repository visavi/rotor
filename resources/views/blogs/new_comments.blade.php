@extends('layout')

@section('title', __('index.blogs') . ' - ' . __('blogs.new_comments') . ' (' . __('main.page_num', ['page' => $comments->currentPage()])  . ')')

@section('header')
    <h1>{{ __('blogs.new_comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.new_comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $comment)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $comment->relate_id }}">{{ $comment->title }}</a> ({{ $comment->count_comments }})

                    @if (isAdmin())
                        <div class="float-end">
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $comment->relate_id }}" data-id="{{ $comment->id }}" data-type="{{ $comment->relate->getMorphClass() }}" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                        </div>
                    @endif
                </div>

                <div class="section-content">
                    {{ bbCode($comment->text) }}<br>

                    {{ __('main.posted') }}: {{ $comment->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($comment->created_at) }}</small><br>
                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">
                            {{ $comment->brow }}, {{ $comment->ip }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('main.empty_comments')) }}
    @endif

    {{ $comments->links() }}
@stop
